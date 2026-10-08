/**
 * The animation is a pure function of time: this module describes the scenes
 * and answers "what is happening at time t" for both the picture and the sound.
 */

export const WIDTH = 1080;
export const HEIGHT = 1920;
export const FPS = 30;
export const SAMPLE_RATE = 48000;

export const TITLE = ['When does a beat', 'become a note?'];
export const STAGES = ['BEAT', 'NOTE', 'CHORD', 'SONG'];

export const PALETTE = {
  background: '#0d0f1c',
  text: '#f4f1ff',
  muted: '#7d7f9e',
  accent: '#7b61ff',
  highlight: '#ffd34d',
  voices: ['#ffb547', '#4fd1ff', '#ff5fa2'],
  beat: '#b6ff5c',
};

const MAJOR = { tint: '#17284d', ringColor: '#4fd1ff' };
const MINOR = { tint: '#331a4a', ringColor: '#ff5fa2' };

/**
 * One chord of the "song" part: each note is the same kick drum, played at
 * rootHz × ratio / ratios[0] hits per second, over a slow 2 hits/s beat.
 */
function chord(label, ratios, rootHz, caption) {
  return {
    label,
    ratios,
    from: rootHz / ratios[0],
    beat: 2,
    ...(ratios[0] === 4 ? MAJOR : MINOR),
    steps: [{ dur: 2, caption }],
  };
}

/**
 * Scenes play one after another. Inside a scene, every voice hits `ratio`
 * times per cycle of a shared "base" clock; steps move that clock from its
 * current speed to `to` (or hold it when `to` is omitted).
 * Captions use *asterisks* for highlighted words.
 */
export const SCENES = [
  {
    ratios: [1],
    from: 2,
    steps: [
      { dur: 3, caption: 'One kick drum. *2 hits* per second' },
      { dur: 9, to: 32, caption: 'Speed it up… when does it turn into a *note*?' },
      { dur: 3, caption: 'There. It’s a *note* now' },
      { dur: 3, to: 64, caption: '*2× faster* = one octave higher' },
      { dur: 1.5, caption: '*2× faster* = one octave higher' },
    ],
  },
  {
    ratios: [3, 2],
    from: 1,
    steps: [
      { dur: 4, caption: 'Now two drums: *3 against 2*' },
      { dur: 6, to: 28, caption: 'Speed them up…' },
      { dur: 3.5, caption: '*3 : 2* = a perfect fifth' },
    ],
  },
  {
    ratios: [4, 5, 6],
    from: 0.5,
    steps: [
      { dur: 4, caption: 'Three drums: *4 : 5 : 6*' },
      { dur: 6, to: 27.5, caption: 'Faster…' },
      { dur: 3.5, caption: '*4 : 5 : 6* = a major chord' },
    ],
  },
  chord('C', [4, 5, 6], 130.81, 'Every sound here is *one kick drum*'),
  chord('Am', [10, 12, 15], 110, 'Every sound here is *one kick drum*'),
  chord('F', [4, 5, 6], 174.61, 'Every sound here is *one kick drum*'),
  chord('G', [4, 5, 6], 196, 'Every sound here is *one kick drum*'),
  chord('C', [4, 5, 6], 130.81, 'Same kick. *Different speeds*'),
  chord('Am', [10, 12, 15], 110, 'Same kick. *Different speeds*'),
  chord('F', [4, 5, 6], 174.61, 'Same kick. *Different speeds*'),
  chord('G', [4, 5, 6], 196, 'Same kick. *Different speeds*'),
];

/** Resolution of the precomputed phase table, in steps per second. */
const PHASE_RATE = 4000;

const clamp01 = (x) => Math.min(1, Math.max(0, x));

/**
 * Base clock speed inside a segment: an eased exponential sweep, so equal
 * times give equal musical intervals (2× speed = +1 octave).
 */
function baseInSegment(seg, t) {
  if (seg.from === seg.to) {
    return seg.from;
  }
  const u = clamp01((t - seg.t0) / (seg.t1 - seg.t0));
  const eased = u * u * (3 - 2 * u);
  return seg.from * Math.pow(seg.to / seg.from, eased);
}

export function buildTimeline(scenes = SCENES) {
  const segments = [];
  let t = 0;

  for (const scene of scenes) {
    const sceneStart = t;
    let base = scene.from;
    for (const step of scene.steps) {
      const to = step.to ?? base;
      segments.push({
        t0: t,
        t1: t + step.dur,
        from: base,
        to,
        scene,
        sceneStart,
        caption: step.caption,
      });
      t += step.dur;
      base = to;
    }
    for (const seg of segments) {
      if (seg.scene === scene) {
        seg.sceneEnd = t;
      }
    }
  }
  const duration = t;

  // A beat keeps its phase across consecutive scenes that share it, and a
  // caption keeps its start time across segments that repeat it.
  segments.forEach((seg, i) => {
    const prev = segments[i - 1];
    const beat = seg.scene.beat;
    seg.beatOrigin = prev && beat && prev.scene.beat === beat ? prev.beatOrigin : seg.sceneStart;
    seg.captionStart = prev && prev.caption === seg.caption ? prev.captionStart : seg.t0;
  });

  function segmentAt(time) {
    const x = Math.min(Math.max(time, 0), duration - 1e-9);
    let lo = 0;
    let hi = segments.length - 1;
    while (lo < hi) {
      const mid = (lo + hi + 1) >> 1;
      if (segments[mid].t0 <= x) {
        lo = mid;
      } else {
        hi = mid - 1;
      }
    }
    return segments[lo];
  }

  const baseAt = (time, seg = segmentAt(time)) => baseInSegment(seg, time);

  // Phase = number of base cycles since the scene started (integral of the
  // base speed), precomputed with the trapezoid rule.
  const table = new Float64Array(Math.ceil(duration * PHASE_RATE) + 2);
  for (let k = 1; k < table.length; k++) {
    const tk = k / PHASE_RATE;
    const prev = (k - 1) / PHASE_RATE;
    const seg = segmentAt(tk);
    if (prev < seg.sceneStart) {
      table[k] = ((tk - seg.sceneStart) * (seg.scene.from + baseAt(tk, seg))) / 2;
    } else {
      table[k] = table[k - 1] + (baseAt(prev) + baseAt(tk, seg)) / (2 * PHASE_RATE);
    }
  }

  function phaseAt(time, seg = segmentAt(time)) {
    const x = Math.min(Math.max(time, 0), duration);
    const k = Math.floor(x * PHASE_RATE);
    const tk = k / PHASE_RATE;
    if (tk < seg.sceneStart) {
      return ((x - seg.sceneStart) * (seg.scene.from + baseAt(x, seg))) / 2;
    }
    return table[k] + ((x - tk) * (baseAt(tk) + baseAt(x, seg))) / 2;
  }

  /** Everything the drawing code needs at time t. */
  function stateAt(time) {
    const segment = segmentAt(time);
    const { scene } = segment;
    const base = baseAt(time, segment);
    const phase = phaseAt(time, segment);
    const voices = scene.ratios.map((ratio, i) => ({
      ratio,
      rate: base * ratio,
      phase: ratio * phase,
      color: PALETTE.voices[i % PALETTE.voices.length],
    }));
    const beat = scene.beat
      ? { rate: scene.beat, phase: scene.beat * (time - segment.beatOrigin), color: PALETTE.beat }
      : null;
    const maxRate = Math.max(...voices.map((v) => v.rate));
    let stage = 'BEAT';
    if (scene.label) {
      stage = 'SONG';
    } else if (voices.length > 1) {
      stage = 'CHORD';
    } else if (maxRate >= 20) {
      stage = 'NOTE';
    }

    return {
      segment,
      scene,
      base,
      phase,
      voices,
      beat,
      maxRate,
      stage,
      caption: segment.caption,
      label: scene.label,
      tint: scene.tint,
      ringColor: scene.ringColor,
    };
  }

  return { duration, segments, segmentAt, baseAt, phaseAt, stateAt };
}

/** Nearest note name for a frequency, e.g. 32.7 → "C1". */
export function noteName(freq) {
  const names = ['C', 'C♯', 'D', 'D♯', 'E', 'F', 'F♯', 'G', 'G♯', 'A', 'A♯', 'B'];
  const midi = Math.round(69 + 12 * Math.log2(freq / 440));
  return names[((midi % 12) + 12) % 12] + (Math.floor(midi / 12) - 1);
}
