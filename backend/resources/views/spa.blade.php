<!doctype html>
<html lang="id">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="description" content="Pazarz — Marketplace Multi-Vendor Premium" />
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet" />
    <title>Pazarz</title>
  </head>
  <body>
    <div id="root"></div>
    @php
      $manifestPath = public_path('build/.vite/manifest.json');
      $js = '';
      $css = '';
      if (file_exists($manifestPath)) {
        $manifest = json_decode(file_get_contents($manifestPath), true);
        $entry = $manifest['src/main.tsx'] ?? null;
        if ($entry) {
          $js = '/build/' . $entry['file'];
          foreach (($entry['css'] ?? []) as $cssFile) {
            $css .= '<link rel="stylesheet" href="/build/' . $cssFile . '">' . "\n    ";
          }
        }
      }
    @endphp
    {!! $css !!}
    @if($js)
      <script type="module" src="{{ $js }}"></script>
    @endif
  </body>
</html>
