@php($fontHref = 'https://fonts.googleapis.com/css2?family=Aref+Ruqaa+Ink:wght@400;700&family=Readex+Pro:wght@400;500;600;700&display=swap')
<link rel="dns-prefetch" href="https://fonts.googleapis.com">
<link rel="dns-prefetch" href="https://fonts.gstatic.com">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="preload" href="{{ $fontHref }}" as="style">
<link href="{{ $fontHref }}" rel="stylesheet" media="print" onload="this.media='all'">
<noscript><link href="{{ $fontHref }}" rel="stylesheet"></noscript>
