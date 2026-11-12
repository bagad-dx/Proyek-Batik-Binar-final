{{-- Tema website Batik Binar: modul CSS yang SAMA PERSIS dengan halaman
  native (frontend/). style.css lama dimuat dulu sebagai fallback agar kelas
  lawas tetap ada; modul website dimuat sesudahnya sehingga menang. --}}
<link rel="stylesheet" href="{{ asset('style.css') }}?v={{ filemtime(public_path('style.css')) }}">
@foreach(['01-base','02-splash','03-header','04-buttons','05-home','06-footer','07-auth','08-components','09-shop','10-gallery','11-overlays','12-dashboard','13-profile','14-animations','15-responsive','16-native'] as $m)
<link rel="stylesheet" href="{{ asset('css/'.$m.'.css') }}?v={{ filemtime(public_path('css/'.$m.'.css')) }}">
@endforeach
