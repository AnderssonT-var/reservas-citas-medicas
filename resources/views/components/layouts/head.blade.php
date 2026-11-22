<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<meta name="color-scheme" content="light dark">
<script>try{document.querySelector('meta[name="color-scheme"]').content=localStorage.getItem('color-scheme')??'light dark'}catch(e){}</script>
<meta name="theme-color" content="#EEF3F1" media="(prefers-color-scheme: light)">
<meta name="theme-color" content="#0B1412" media="(prefers-color-scheme: dark)">
<meta name="csrf-token" content="{{ csrf_token() }}">
<meta name="description" content="Reserva tu cita médica en línea en {{ $clinica['nombre_clinica'] }}: elige especialidad, doctor y hora en un minuto.">
<link rel="icon" href="/favicon.svg" type="image/svg+xml">
<link rel="preconnect" href="https://fonts.bunny.net">
<link href="https://fonts.bunny.net/css?family=bricolage-grotesque:500,600,700|instrument-sans:400,500,600,700&display=swap" rel="stylesheet">
@vite(['resources/css/app.css', 'resources/js/app.js'])
