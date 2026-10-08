/**
 * Every sound in the animation is the same synthetic kick drum, repeated at
 * different speeds. Above ~20 hits per second the ear stops hearing separate
 * hits and hears a pitch instead: that is the whole point of the video.
 *
 * Works in the browser and in Node (no DOM, no Web Audio needed).
 */

const ATTACK = 0.002; // s, avoids a click at the start of each hit
const DECAY = 0.07; // s, amplitude decay of one hit
const TAIL = 0.25; // s, after this a hit is considered silent
const SWEEP = 0.03; // s, speed of the pitch drop
const F_START = 155; // Hz, pitch at the start of a hit
const F_END = 45; // Hz, pitch the hit settles on
const SCENE_FADE = 0.015; // s, fade at scene changes to avoid clicks

/** One kick drum hit, `tau` seconds after it started. */
export function kick(tau) {
  if (tau < 0 || tau > TAIL) {
    return 0;
  }
  const attack = tau < ATTACK ? tau / ATTACK : 1;
  // Phase of a sine whose frequency slides from F_START down to F_END.
  const cycles = F_END * tau + (F_START - F_END) * SWEEP * (1 - Math.exp(-tau / SWEEP));
  return attack * Math.exp(-tau / DECAY) * Math.sin(2 * Math.PI * cycles);
}

/**
 * A train of hits. `phase` counts hits since the voice started (hit n happens
 * when phase crosses n), `rate` is the current number of hits per second.
 */
export function train(phase, rate) {
  if (phase < 0) {
    return 0;
  }
  const sinceLast = phase - Math.floor(phase);
  // Never sum hits from before the voice started.
  const maxHits = Math.min(Math.floor(phase), Math.ceil(TAIL * rate));
  let sum = 0;
  for (let k = 0; k <= maxHits; k++) {
    sum += kick((sinceLast + k) / rate);
  }
  return sum;
}

/** Overlapping hits add up: keep fast voices at a similar loudness. */
const voiceGain = (rate) => 1 / Math.max(1, rate * DECAY);

/**
 * Fill `out[from..to)` with the mono soundtrack of `timeline`.
 * Rendering can be split in chunks (the browser does this to stay responsive).
 */
export function renderAudio(
  timeline,
  sampleRate,
  out = new Float32Array(Math.ceil(timeline.duration * sampleRate)),
  from = 0,
  to = out.length,
) {
  for (let i = from; i < to; i++) {
    const t = i / sampleRate;
    const seg = timeline.segmentAt(t);
    const { scene } = seg;
    const base = timeline.baseAt(t, seg);
    const phase = timeline.phaseAt(t, seg);

    let voices = 0;
    for (const ratio of scene.ratios) {
      const rate = base * ratio;
      voices += train(ratio * phase, rate) * voiceGain(rate);
    }
    const fade = Math.min(1, (t - seg.sceneStart) / SCENE_FADE, (seg.sceneEnd - t) / SCENE_FADE);
    let sample = (voices * Math.max(0, fade) * 0.8) / Math.sqrt(scene.ratios.length);

    if (scene.beat) {
      sample += train(scene.beat * (t - seg.beatOrigin), scene.beat) * 0.9;
    }
    // Soft clipping keeps peaks under control without harsh distortion.
    out[i] = Math.tanh(sample * 1.2) * 0.85;
  }
  return out;
}

/** Wrap rendered samples so the drawing code can read the signal at any time. */
export function createAudioTrack(samples, sampleRate) {
  return {
    samples,
    sampleRate,
    sampleAt(t) {
      const x = t * sampleRate;
      if (x < 0 || x >= samples.length - 1) {
        return 0;
      }
      const i = Math.floor(x);
      const f = x - i;
      return samples[i] * (1 - f) + samples[i + 1] * f;
    },
  };
}
