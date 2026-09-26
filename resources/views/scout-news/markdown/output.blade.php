# ニュース一覧：{{ $generatedAt }}

🏢 プロバイダー: {{ is_null($provider) ? '指定なし' : $provider->value }} / 🤖 モデル: {{ is_null($model) ? '指定なし' : $model }} / 🌍 地域指定: ({{ $location['city'] }}/{{ $location['region'] }}/{{ $location['country'] }})

🕵️ {{ $count }}件のニュースをピックアップしました。

@foreach ($topicsCount as $topic => $count)
🚩 {{ $topic }}: {{ $count }}件
@endforeach

@foreach ($articles as $topic => $items)
## 🚩 {{ $topic }}

@foreach ($items as $article)
### 📰 {{ $article['title'] }}

- 📅 日付: {{ $article['date'] }}
- 📰 出典: {{ $article['source'] }}
- 🔗 URL: {{ $article['url'] }}

{{ $article['summary'] }}

@endforeach
@endforeach
