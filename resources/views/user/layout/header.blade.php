<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
 <title>@yield('meta_title', 'MENTORS SPORTS KARATE - DO')</title>

<meta name="description" content="The Mentors Sports Karate-Do brings certified karate instructors directly to schools across India. We provide structured karate coaching, grading, and events to build discipline, confidence, and fitness in students.">
<meta name="keywords" content="@yield('meta_keywords', 'karate coaching in schools, karate classes India, school karate programs, karate for kids, karate grading, karate events India, karate training for students, karate fitness discipline confidence')">
<meta name="author" content="Mentors Sports Karate Do">

<!-- Open Graph Tags -->
<meta property="og:title" content="MENTORS SPORTS KARATE - DO" />
<meta property="og:description" content="The Mentors Sports Karate-Do brings certified karate instructors directly to schools across India. We provide structured karate coaching, grading, and events to build discipline, confidence, and fitness in students." />
<meta property="og:type" content="website" />
<meta property="og:url" content="{{ url()->current() }}" />
<meta property="og:image" content="@yield('meta_image', asset('assets/admin/images/karate_logo.png'))" />

<!-- Twitter Card Tags (optional but recommended) -->
<meta name="twitter:card" content="summary_large_image" />
<meta name="twitter:title" content="@yield('meta_title', 'MENTORS SPORTS KARATE - DO')" />
<meta name="twitter:description" content="The Mentors Sports Karate-Do brings certified karate instructors directly to schools across India. We provide structured karate coaching, grading, and events to build discipline, confidence, and fitness in students." />
<meta name="twitter:image" content="@yield('meta_image', asset('assets/images/default-og-image.jpg'))" />

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ asset('assets/admin/images/karate_logo.png') }}">

    <link rel="stylesheet" href="{{ asset('assets/user/css/style1.css') }}">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

</head>
