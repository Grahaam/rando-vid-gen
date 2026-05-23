import React, { useState, useEffect } from 'react';
import { Dice5, Instagram, Youtube, Twitter, Music2 } from 'lucide-react';

const JSON_URL = "https://raw.githubusercontent.com/YOUR_USER/YOUR_REPO/main/videos.json";

export default function App() {
  const [videos, setVideos] = useState([]);
  const [loading, setLoading] = useState(true);
  const [currentVideo, setCurrentVideo] = useState(null);
  const [filters, setFilters] = useState({
    tiktok: true,
    instagram: true,
    x: true,
    youtube: true
  });

  useEffect(() => {
    fetch(JSON_URL)
      .then(res => res.json())
      .then(data => {
        setVideos(data);
        setLoading(false);
      })
      .catch(err => console.error("Error fetching videos:", err));
  }, []);

  const rollDice = () => {
    const filtered = videos.filter(v => filters[v.platform] !== false || v.platform === 'other');
    if (filtered.length === 0) return alert("No videos found for selected platforms!");
    const randomIndex = Math.floor(Math.random() * filtered.length);
    setCurrentVideo(filtered[randomIndex]);
  };

  const togglePlatform = (platform) => {
    setFilters(prev => ({ ...prev, [platform]: !prev[platform] }));
  };

  if (loading) return <div className="h-screen flex items-center justify-center bg-black text-white">Loading Database...</div>;

  return (
    <div className="min-h-screen bg-slate-950 text-slate-100 p-6 font-sans">
      <div className="max-w-md mx-auto space-y-8">
        <header className="text-center space-y-2">
          <h1 className="text-4xl font-black tracking-tighter text-transparent bg-clip-text bg-gradient-to-r from-purple-400 to-pink-600">
            RANDOM VIDEO
          </h1>
        </header>

        <div className="grid grid-cols-2 gap-3">
          {[
            { id: 'tiktok', icon: <Music2 size={18}/>, color: 'text-pink-500' },
            { id: 'instagram', icon: <Instagram size={18}/>, color: 'text-orange-500' },
            { id: 'x', icon: <Twitter size={18}/>, color: 'text-blue-400' },
            { id: 'youtube', icon: <Youtube size={18}/>, color: 'text-red-500' }
          ].map(p => (
            <button
              key={p.id}
              onClick={() => togglePlatform(p.id)}
              className={`flex items-center justify-center gap-2 p-3 rounded-xl border transition-all ${
                filters[p.id] ? 'border-slate-700 bg-slate-800' : 'border-transparent bg-slate-900 opacity-40'
              } ${p.color}`}
            >
              {p.icon} <span className="capitalize font-medium">{p.id}</span>
            </button>
          ))}
        </div>

        <button
          onClick={rollDice}
          className="w-full py-4 bg-gradient-to-r from-purple-600 to-blue-600 rounded-2xl font-bold text-xl shadow-lg shadow-purple-500/20 active:scale-95 transition-transform flex items-center justify-center gap-3"
        >
          <Dice5 size={28} /> ROLL THE DICE
        </button>

        {currentVideo && (
          <div className="bg-slate-900 border border-slate-800 rounded-3xl p-6 animate-in fade-in zoom-in duration-300">
            <p className="text-xs font-bold text-slate-500 uppercase tracking-widest mb-2">{currentVideo.platform}</p>
            <h2 className="text-lg font-semibold leading-tight mb-4 line-clamp-2">{currentVideo.title}</h2>
            <a
              href={currentVideo.url}
              target="_blank"
              rel="noopener noreferrer"
              className="block w-full py-3 bg-slate-800 hover:bg-slate-700 text-center rounded-xl font-medium transition-colors"
            >
              Watch Video $\rightarrow$
            </a>
          </div>
        )}
      </div>
    </div>
  );
}
