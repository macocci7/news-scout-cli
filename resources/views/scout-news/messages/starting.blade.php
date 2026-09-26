🕵️ 過去{{ $days }}日分のニュースを最大{{ $maxResults }}件リストアップします。

🌐 地域指定： {{ implode("/", $location) }}
📰 関連トピック：
@foreach ($topics as $topic)
  🚩 {{ $topic }}
@endforeach
