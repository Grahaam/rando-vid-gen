import yt_dlp
import json
import os

SEARCH_QUERIES = ["ytsearch10:funny shorts", "tiktok trending"]
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
    ydl_opts = {'quiet': True, 'extract_flat': True}
    with yt_dlp.YoutubeDL(ydl_opts) as ydl:
        for query in SEARCH_QUERIES:
            try:
                info = ydl.extract_info(query, download=False)
                if 'entries' in info:
                    for entry in info['entries']:
                        if entry:
                            url = entry.get('url') or entry.get('webpage_url')
                            video_list.append({
                                "id": entry.get('id'),
                                "title": entry.get('title', 'Untitled'),
                                "platform": "other",
                                "url": transform_url(url)
                            })
            except Exception as e: print(f"Error: {e}")
    
    with open('../videos.json', 'w') as f:
        json.dump(video_list, f, indent=4)

if __name__ == "__main__":
    scrape_videos()
