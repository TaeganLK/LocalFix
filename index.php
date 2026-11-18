<?php

require_once __DIR__ . '/includes/auth.php';

$isLoggedIn = isLoggedIn();
$isAdmin = $isLoggedIn && ($_SESSION['role'] ?? '') === 'admin';

if ($isLoggedIn) {
    $dashboardLink = $isAdmin
        ? 'admin/dashboard.php'
        : 'dashboard.php';

    $dashboardLabel = 'Go to Dashboard';
} else {
    $dashboardLink = 'login.php';
    $dashboardLabel = 'Login';
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>LocalFix | Smarter Municipal Reporting</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <link rel="stylesheet" href="assets/css/style.css">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">

    <style>
        :root {
            --urban-navy: #101D32;
            --urban-navy-light: #1B2B43;
            --urban-cyan: #35BFEA;
            --urban-teal: #00BFA6;
            --urban-text: #17283E;
            --urban-muted: #718096;
            --urban-bg: #F3F6FA;
        }

        body {
            background: var(--urban-bg);
            font-family: 'Inter', sans-serif;
            overflow-x: hidden;
        }

        /* NAVIGATION */

        .navbar {
            background: rgba(16, 29, 50, 0.97) !important;
            backdrop-filter: blur(14px);
            border-bottom: 1px solid rgba(53, 191, 234, 0.2);
            padding: 16px 0;
        }

        .navbar-brand {
            font-size: 1.5rem;
            font-weight: 900;
            letter-spacing: -1px;
        }

        .brand-mark {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 35px;
            height: 35px;
            margin-right: 9px;
            border-radius: 10px;
            background: linear-gradient(135deg, var(--urban-cyan), var(--urban-teal));
            color: var(--urban-navy);
            font-weight: 900;
            font-size: 1.1rem;
        }

        .navbar .nav-link {
            font-size: 0.9rem;
            font-weight: 600;
        }

        .nav-cta {
            background: linear-gradient(110deg, var(--urban-cyan), var(--urban-teal));
            color: var(--urban-navy) !important;
            border-radius: 8px;
            padding: 10px 17px !important;
            font-weight: 800 !important;
        }

        .nav-cta:hover {
            background: #FFFFFF;
            color: var(--urban-navy) !important;
        }

        /* HERO */

        .hero {
            position: relative;
            overflow: hidden;
            min-height: 610px;
            display: flex;
            align-items: center;
            color: white;
            background:
                radial-gradient(ellipse at 78% 45%, rgba(0, 191, 166, 0.17), transparent 38%),
                radial-gradient(ellipse at 10% 10%, rgba(53, 191, 234, 0.12), transparent 35%),
                linear-gradient(120deg, #101D32 0%, #152B45 60%, #102B3D 100%);
        }

        .hero::before {
            content: "";
            position: absolute;
            inset: 0;
            opacity: 0.16;
            background-image:
                linear-gradient(rgba(53, 191, 234, 0.16) 1px, transparent 1px),
                linear-gradient(90deg, rgba(53, 191, 234, 0.16) 1px, transparent 1px);
            background-size: 48px 48px;
            mask-image: linear-gradient(to right, transparent, black);
        }

        .hero::after {
            content: "";
            position: absolute;
            width: 430px;
            height: 430px;
            right: -180px;
            top: -200px;
            border: 1px solid rgba(53, 191, 234, 0.16);
            border-radius: 50%;
            box-shadow:
                0 0 0 45px rgba(53, 191, 234, 0.025),
                0 0 0 90px rgba(53, 191, 234, 0.025);
        }

        .hero-content {
            position: relative;
            z-index: 2;
            padding: 85px 0;
        }

        .hero-label {
            display: inline-flex;
            align-items: center;
            gap: 9px;
            padding: 8px 13px;
            border: 1px solid rgba(53, 191, 234, 0.35);
            border-radius: 30px;
            background: rgba(53, 191, 234, 0.08);
            color: #9AE7F8;
            font-size: 0.76rem;
            font-weight: 700;
            letter-spacing: 1.4px;
            text-transform: uppercase;
        }

        .status-dot {
            width: 7px;
            height: 7px;
            background: var(--urban-teal);
            border-radius: 50%;
            box-shadow: 0 0 12px var(--urban-teal);
        }

        .hero h1 {
            color: #FFFFFF;
            font-size: clamp(2.7rem, 5vw, 4.6rem);
            font-weight: 900;
            line-height: 1.08;
            letter-spacing: -3px;
            margin-top: 27px;
            max-width: 700px;
        }

        .hero h1 span {
            background: linear-gradient(90deg, #35BFEA, #00D7B5);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }

        .hero-description {
            max-width: 570px;
            margin-top: 25px;
            color: #B8C9D9;
            font-size: 1.08rem;
            line-height: 1.85;
        }

        .hero-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 13px;
            margin-top: 34px;
        }

        .hero-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 14px 23px;
            border-radius: 9px;
            font-weight: 800;
            transition: all 0.25s ease;
        }

        .hero-btn-primary {
            color: #10233A;
            background: linear-gradient(110deg, #35BFEA, #00BFA6);
            box-shadow: 0 8px 25px rgba(0, 191, 166, 0.2);
        }

        .hero-btn-primary:hover {
            color: #10233A;
            transform: translateY(-3px);
            box-shadow: 0 12px 30px rgba(0, 191, 166, 0.3);
        }

        .hero-btn-secondary {
            color: #FFFFFF;
            border: 1px solid rgba(255, 255, 255, 0.3);
            background: rgba(255, 255, 255, 0.04);
        }

        .hero-btn-secondary:hover {
            color: var(--urban-cyan);
            border-color: var(--urban-cyan);
            background: rgba(53, 191, 234, 0.08);
        }

        .hero-note {
            margin-top: 27px;
            color: #91A6BB;
            font-size: 0.83rem;
        }

        /* SMART CITY VISUAL */

        .city-visual {
            position: relative;
            z-index: 2;
            min-height: 410px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .city-panel {
            position: relative;
            width: 100%;
            max-width: 470px;
            min-height: 340px;
            padding: 24px;
            border: 1px solid rgba(94, 204, 230, 0.28);
            border-radius: 20px;
            background: rgba(17, 38, 58, 0.83);
            box-shadow: 0 25px 70px rgba(0, 0, 0, 0.3);
            backdrop-filter: blur(12px);
        }

        .city-panel-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-bottom: 17px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .city-panel-title {
            color: #FFFFFF;
            font-weight: 800;
            font-size: 0.95rem;
        }

        .live-indicator {
            color: #5DE0C1;
            font-size: 0.72rem;
            font-weight: 700;
        }

        .city-grid {
            position: relative;
            height: 220px;
            margin-top: 18px;
            overflow: hidden;
            border-radius: 12px;
            background-color: #142B42;
            background-image:
                linear-gradient(rgba(53, 191, 234, 0.12) 1px, transparent 1px),
                linear-gradient(90deg, rgba(53, 191, 234, 0.12) 1px, transparent 1px);
            background-size: 27px 27px;
        }

        .city-road {
            position: absolute;
            background: #263F58;
            border: 1px solid rgba(53, 191, 234, 0.2);
        }

        .road-one {
            width: 120%;
            height: 28px;
            top: 47%;
            left: -10%;
            transform: rotate(-17deg);
        }

        .road-two {
            width: 27px;
            height: 130%;
            top: -15%;
            left: 45%;
            transform: rotate(22deg);
        }

        .road-three {
            width: 100%;
            height: 19px;
            top: 20%;
            left: 10%;
            transform: rotate(29deg);
        }

        .city-building {
            position: absolute;
            bottom: 0;
            width: 37px;
            border: 1px solid rgba(53, 191, 234, 0.25);
            background: linear-gradient(180deg, #244765, #18334D);
        }

        .city-building::after {
            content: "";
            position: absolute;
            inset: 8px 6px;
            background-image: repeating-linear-gradient(
                0deg,
                rgba(53, 191, 234, 0.55) 0px,
                rgba(53, 191, 234, 0.55) 3px,
                transparent 3px,
                transparent 10px
            );
        }

        .building-one {
            height: 88px;
            left: 12%;
        }

        .building-two {
            height: 126px;
            left: 25%;
            width: 44px;
        }

        .building-three {
            height: 70px;
            right: 18%;
        }

        .building-four {
            height: 108px;
            right: 7%;
            width: 43px;
        }

        .map-marker {
            position: absolute;
            z-index: 3;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 32px;
            height: 32px;
            border: 2px solid #FFFFFF;
            border-radius: 50%;
            background: var(--urban-teal);
            color: var(--urban-navy);
            font-weight: 900;
            font-size: 0.8rem;
            box-shadow: 0 0 0 7px rgba(0, 191, 166, 0.13), 0 0 22px rgba(0, 191, 166, 0.5);
        }

        .marker-one {
            top: 27%;
            left: 32%;
        }

        .marker-two {
            top: 61%;
            right: 27%;
            background: var(--urban-cyan);
            box-shadow: 0 0 0 7px rgba(53, 191, 234, 0.13), 0 0 22px rgba(53, 191, 234, 0.5);
        }

        .city-panel-footer {
            display: flex;
            justify-content: space-between;
            gap: 12px;
            margin-top: 18px;
        }

        .city-stat {
            flex: 1;
        }

        .city-stat strong {
            display: block;
            color: #FFFFFF;
            font-size: 1.1rem;
        }

        .city-stat span {
            color: #91A6BB;
            font-size: 0.7rem;
        }

        /* SECTION COMMON */

        .section-padding {
            padding: 85px 0;
        }

        .section-eyebrow {
            color: #159CC8;
            font-size: 0.76rem;
            font-weight: 800;
            letter-spacing: 1.8px;
            text-transform: uppercase;
        }

        .section-heading {
            margin-top: 12px;
            color: var(--urban-navy);
            font-size: clamp(1.9rem, 3vw, 2.65rem);
            font-weight: 900;
            letter-spacing: -1.5px;
        }

        .section-description {
            max-width: 620px;
            margin: 15px auto 0;
            color: #718096;
            line-height: 1.8;
        }

        /* HOW IT WORKS */

        .feature-card {
            position: relative;
            height: 100%;
            padding: 29px;
            border: 1px solid #E2E9F0;
            border-radius: 16px;
            background: #FFFFFF;
            box-shadow: 0 5px 22px rgba(16, 29, 50, 0.045);
            transition: all 0.25s ease;
        }

        .feature-card:hover {
            transform: translateY(-7px);
            border-color: rgba(53, 191, 234, 0.5);
            box-shadow: 0 17px 38px rgba(16, 29, 50, 0.09);
        }

        .feature-number {
            color: #DCEAF2;
            font-size: 2.8rem;
            font-weight: 900;
            line-height: 1;
        }

        .feature-icon {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 53px;
            height: 53px;
            margin: 22px 0;
            border-radius: 13px;
            background: linear-gradient(135deg, #E4F7FC, #E3F8F3);
            color: #159CC8;
            font-size: 1.5rem;
            font-weight: 800;
        }

        .feature-card h5 {
            color: var(--urban-navy);
            font-weight: 800;
            margin-bottom: 11px;
        }

        .feature-card p {
            color: #718096;
            font-size: 0.92rem;
            line-height: 1.8;
            margin-bottom: 0;
        }

        /* CATEGORIES */

        .categories-section {
            background: #EAF0F6;
        }

        .category-card {
            display: flex;
            align-items: center;
            gap: 15px;
            height: 100%;
            padding: 20px;
            border: 1px solid #DFE7EF;
            border-radius: 13px;
            background: #FFFFFF;
            transition: all 0.23s ease;
        }

        .category-card:hover {
            transform: translateY(-4px);
            border-color: var(--urban-cyan);
            box-shadow: 0 12px 25px rgba(16, 29, 50, 0.07);
        }

        .category-icon {
            flex-shrink: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 46px;
            height: 46px;
            border-radius: 12px;
            background: #E5F7FC;
            color: #159CC8;
            font-size: 1.25rem;
            font-weight: 800;
        }

        .category-card h5 {
            color: var(--urban-navy);
            font-size: 0.95rem;
            font-weight: 800;
            margin: 0 0 4px;
        }

        .category-card p {
            color: #718096;
            font-size: 0.79rem;
            margin: 0;
        }

        /* CALL TO ACTION */

        .cta-section {
            position: relative;
            overflow: hidden;
            padding: 75px 0;
            color: #FFFFFF;
            background:
                radial-gradient(circle at 90% 10%, rgba(53, 191, 234, 0.2), transparent 30%),
                linear-gradient(120deg, #101D32, #1B3B52);
        }

        .cta-section h2 {
            color: #FFFFFF;
            font-size: clamp(1.8rem, 3vw, 2.6rem);
            font-weight: 900;
            letter-spacing: -1px;
        }

        .cta-section p {
            color: #B8C9D9;
            max-width: 650px;
            margin: 17px auto 0;
            line-height: 1.8;
        }

        /* FOOTER */

        footer {
            background: #0B1728;
            padding: 34px 0;
            color: #FFFFFF;
            border-top: 1px solid rgba(53, 191, 234, 0.2);
        }

        .footer-brand {
            font-size: 1.3rem;
            font-weight: 900;
            letter-spacing: -0.5px;
        }

        footer p {
            color: #91A6BB;
            font-size: 0.84rem;
        }

        .footer-divider {
            height: 1px;
            margin: 22px 0;
            background: rgba(255, 255, 255, 0.1);
        }

        /* RESPONSIVE */

        @media (max-width: 991px) {
            .hero {
                min-height: auto;
            }

            .hero-content {
                padding: 75px 0 35px;
            }

            .city-visual {
                min-height: 360px;
                padding-bottom: 35px;
            }

            .hero h1 {
                letter-spacing: -2px;
            }
        }

        @media (max-width: 576px) {
            .hero-content {
                padding-top: 60px;
            }

            .hero h1 {
                font-size: 2.65rem;
                letter-spacing: -1.8px;
            }

            .hero-description {
                font-size: 0.98rem;
            }

            .hero-btn {
                width: 100%;
            }

            .city-panel {
                padding: 16px;
            }

            .city-visual {
                min-height: 310px;
            }

            .city-grid {
                height: 190px;
            }

            .section-padding {
                padding: 65px 0;
            }
        }
    </style>
</head>

<body>

<!-- NAVIGATION -->

<nav class="navbar navbar-expand-lg navbar-dark">
    <div class="container">

        <a class="navbar-brand d-flex align-items-center" href="index.php">
            <span class="brand-mark">L</span>
            LocalFix
        </a>

        <button
            class="navbar-toggler"
            type="button"
            data-bs-toggle="collapse"
            data-bs-target="#navbarContent"
            aria-controls="navbarContent"
            aria-expanded="false"
            aria-label="Toggle navigation"
        >
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarContent">

            <ul class="navbar-nav ms-auto align-items-lg-center gap-lg-2 mt-3 mt-lg-0">

                <li class="nav-item">
                    <a class="nav-link" href="#how-it-works">How It Works</a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="#fault-categories">Fault Categories</a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="#about-localfix">About LocalFix</a>
                </li>

                <li class="nav-item ms-lg-2">
                    <a class="nav-link nav-cta"
                       href="<?= htmlspecialchars($dashboardLink) ?>">
                        <?= htmlspecialchars($dashboardLabel) ?>
                    </a>
                </li>

                <?php if (!$isLoggedIn): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="register.php">Create Account</a>
                    </li>
                <?php endif; ?>

            </ul>

        </div>
    </div>
</nav>

<!-- HERO -->

<section class="hero">

    <div class="container hero-content">

        <div class="row align-items-center g-5">

            <div class="col-lg-7">

                <div class="hero-label">
                    <span class="status-dot"></span>
                    SMARTER COMMUNITY REPORTING
                </div>

                <h1>
                    Better Cities Start With <span>Better Reporting.</span>
                </h1>

                <p class="hero-description">
                    A smarter way to report municipal faults, keep track of
                    submissions and stay informed about the progress of
                    community service requests.
                </p>

                <div class="hero-actions">

                    <?php if ($isLoggedIn): ?>

                        <a href="<?= htmlspecialchars($dashboardLink) ?>"
                           class="hero-btn hero-btn-primary">
                            Open Dashboard &rarr;
                        </a>

                    <?php else: ?>

                        <a href="register.php"
                           class="hero-btn hero-btn-primary">
                            Get Started &rarr;
                        </a>

                        <a href="login.php"
                           class="hero-btn hero-btn-secondary">
                            Sign In
                        </a>

                    <?php endif; ?>

                </div>

                <p class="hero-note">
                    A central platform for reporting and tracking municipal faults.
                </p>

            </div>

            <!-- SMART CITY VISUAL -->

            <div class="col-lg-5">

                <div class="city-visual">

                    <div class="city-panel">

                        <div class="city-panel-header">
                            <span class="city-panel-title">
                                Community Overview
                            </span>

                            <span class="live-indicator">
                                ● LOCALFIX
                            </span>
                        </div>

                        <div class="city-grid">

                            <div class="city-road road-one"></div>
                            <div class="city-road road-two"></div>
                            <div class="city-road road-three"></div>

                            <div class="city-building building-one"></div>
                            <div class="city-building building-two"></div>
                            <div class="city-building building-three"></div>
                            <div class="city-building building-four"></div>

                            <div class="map-marker marker-one">!</div>
                            <div class="map-marker marker-two">!</div>

                        </div>

                        <div class="city-panel-footer">

                            <div class="city-stat">
                                <strong>REPORT</strong>
                                <span>Submit a fault</span>
                            </div>

                            <div class="city-stat">
                                <strong>TRACK</strong>
                                <span>Follow progress</span>
                            </div>

                            <div class="city-stat">
                                <strong>CONNECT</strong>
                                <span>Support communities</span>
                            </div>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>

</section>

<!-- HOW IT WORKS -->

<section class="section-padding" id="how-it-works">

    <div class="container">

        <div class="text-center mb-5">

            <div class="section-eyebrow">SIMPLE. CLEAR. CONNECTED.</div>

            <h2 class="section-heading">How LocalFix Works</h2>

            <p class="section-description">
                From identifying a municipal fault to tracking its progress,
                LocalFix makes the reporting process straightforward.
            </p>

        </div>

        <div class="row g-4">

            <div class="col-md-4">

                <div class="feature-card">

                    <div class="feature-number">01</div>

                    <div class="feature-icon">&#9998;</div>

                    <h5>Submit a Report</h5>

                    <p>
                        Register and submit a municipal fault by selecting
                        a category, providing its location and describing
                        the problem.
                    </p>

                </div>

            </div>

            <div class="col-md-4">

                <div class="feature-card">

                    <div class="feature-number">02</div>

                    <div class="feature-icon">&#9776;</div>

                    <h5>Receive a Reference</h5>

                    <p>
                        Each submitted report receives a unique reference
                        number that residents can use to identify and
                        track their submission.
                    </p>

                </div>

            </div>

            <div class="col-md-4">

                <div class="feature-card">

                    <div class="feature-number">03</div>

                    <div class="feature-icon">&#8599;</div>

                    <h5>Track Progress</h5>

                    <p>
                        View submitted reports and check their latest
                        status updates through your LocalFix account.
                    </p>

                </div>

            </div>

        </div>

    </div>

</section>

<!-- FAULT CATEGORIES -->

<section class="section-padding categories-section" id="fault-categories">

    <div class="container">

        <div class="text-center mb-5">

            <div class="section-eyebrow">COMMUNITY INFRASTRUCTURE</div>

            <h2 class="section-heading">What Can You Report?</h2>

            <p class="section-description">
                LocalFix supports several categories of municipal
                infrastructure and service-related faults.
            </p>

        </div>

        <div class="row g-3">

            <div class="col-md-6 col-lg-4">
                <div class="category-card">
                    <div class="category-icon">&#9632;</div>
                    <div>
                        <h5>Potholes</h5>
                        <p>Damaged roads and potholes.</p>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-lg-4">
                <div class="category-card">
                    <div class="category-icon">&#9733;</div>
                    <div>
                        <h5>Streetlights</h5>
                        <p>Broken or faulty streetlights.</p>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-lg-4">
                <div class="category-card">
                    <div class="category-icon">&#9673;</div>
                    <div>
                        <h5>Water Leaks</h5>
                        <p>Water leaks and burst pipes.</p>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-lg-4">
                <div class="category-card">
                    <div class="category-icon">&#8776;</div>
                    <div>
                        <h5>Blocked Drains</h5>
                        <p>Blocked or damaged drainage systems.</p>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-lg-4">
                <div class="category-card">
                    <div class="category-icon">&#10006;</div>
                    <div>
                        <h5>Illegal Dumping</h5>
                        <p>Unauthorised waste disposal.</p>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-lg-4">
                <div class="category-card">
                    <div class="category-icon">&#8644;</div>
                    <div>
                        <h5>Traffic Lights</h5>
                        <p>Faulty or damaged traffic lights.</p>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-lg-4">
                <div class="category-card">
                    <div class="category-icon">&#8943;</div>
                    <div>
                        <h5>Other</h5>
                        <p>Other municipal faults and issues.</p>
                    </div>
                </div>
            </div>

        </div>

    </div>

</section>

<!-- ABOUT / CTA -->

<section class="cta-section" id="about-localfix">

    <div class="container text-center">

        <div class="section-eyebrow" style="color: #72DDF1;">
            COMMUNITY. CONNECTION. PROGRESS.
        </div>

        <h2 class="mt-3">Help Make Your Community Better.</h2>

        <p>
            LocalFix provides a central platform for residents to submit
            and monitor municipal fault reports through one organised system.
        </p>

        <?php if (!$isLoggedIn): ?>

            <a href="register.php" class="hero-btn hero-btn-primary mt-4">
                Create Your Account &rarr;
            </a>

        <?php else: ?>

            <a href="<?= htmlspecialchars($dashboardLink) ?>"
               class="hero-btn hero-btn-primary mt-4">
                Open Your Dashboard &rarr;
            </a>

        <?php endif; ?>

    </div>

</section>

<!-- FOOTER -->

<footer>

    <div class="container">

        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">

            <div>
                <div class="footer-brand">
                    <span style="color: #35BFEA;">L.</span> LocalFix
                </div>

                <p class="mt-2 mb-0">
                    Web-Based Municipal Fault Reporting and Service Request Management System
                </p>
            </div>

            <div class="text-md-end">
                <p class="footer-text">
                    LocalFix is an academic project developed as part of the
                    Bachelor of Science in Information Technology programme at
                    Richfield Graduate Institute of Technology.
                </p>
            </div>

        </div>

        <div class="footer-divider"></div>

        <p class="mb-0 text-center">
            Designed to make municipal fault reporting simpler.
        </p>

    </div>

</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
