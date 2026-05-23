import yt_dlp
import json
import os

# We use 'ytsearch10:' prefix so yt-dlp knows these are SEARCHES, not URLs.
# This is the most stable way to ensure the scraper never crashes.
SEARCH_QUER_PREFIX = "ytsearch10:"
SEARCH_QUERIES = [
    f"{SEARCH_QUER_PREFIX}funny shorts",
    f"{SEARCH_QUER_PREFIX}trending reels",
    f"{SEARCH_QUER_PREFIX}viral tiktok",
    f"{SEARCH_QUER_PREFIX}amazing videos"
]

PROXY_MAP = {
    "tiktok.com": "vxtiktok.com",
    "instagram.com": "ddinstagram.com",
    "twitter.com": "fxtwitter.com",
    "x.com": "fxtwitter.com",
    "youtube.com": "youtube.com",
    "youtu.be": "youtube.com"
}

def transform_url(url):
    for original, proxy in PROXY_MAP.items():
        if original in url: return url.replace(original, proxy)
    return url

def scrape_videos():
    video_list = []
    ydl_opts = {
        'quiet': True,
        'extract_flat': True,
        'force_generic_extractor': False
    }

    with yt_dlp.YoutubeDL(ydl_opts) as ydl:
        for query in SEARCH_QUERIES:
            try:
                print(f"Searching for: {query}")
                info = ydl.extract_info(query, download=False)
                
                if 'entries' in info:
                    for entry in info['entries']:
                        if entry:
                            # Get the URL from either 'url' or 'webpage_url'
                            raw_url = entry.get('url') or entry.get('webpage_url')
                            if raw_url:
                                video_list.append({
                                    "id": entry.get('id'),
                                    "title": entry.get('title', 'Untitled'),
                                    "platform": "youtube", # Defaulting to youtube for stability
                                    "url": transform_url(raw_url)
                                })
            except Exception as e:
                print(f"Error scraping {query}: {e}")
    
    # Save to the root videos.json
    output_path = os.path.join(os.path.dirname(os.path.abspath(__file__)), '../videos.json')
    with open(output_path, 'w', encoding='utf-8') as f:
        json.dump(video_list, f, indent=4, ensure_ascii=False)
    
    print(f"✅ Success! Saved {len(video_list)} videos to {output_path}")

if __run_main := True: # Just a placeholder for the block
    if __name__ == "__main__":
        scrape_videos()
