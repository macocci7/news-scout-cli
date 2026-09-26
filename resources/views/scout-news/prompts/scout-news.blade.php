次のトピックに関する過去{{ $days }}日以内のニュース記事を最大{{ $maxResults }}件リストしてください。

## トピック一覧
@foreach ($topics as $topic)
- {{ $topic }}
@endforeach
