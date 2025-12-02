<?php
session_start();
include 'db.php';

$pagina_titulo = "Campaña GM Fitness & Nutrition";
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pagina_titulo; ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"/>
    <style>
        :root {
            --primary: #2C5364;
            --secondary: #203A43;
            --accent: #FFD700;
            --accent-dark: #FFB347;
            --gradient: linear-gradient(135deg, #0F2027, #203A43, #2C5364);
            --gradient-accent: linear-gradient(135deg, #FFD700, #FFB347);
            --white: #FFFFFF;
            --light-bg: #F8F9FA;
            --text-dark: #203A43;
            --text-light: #6C757D;
            --shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            --shadow-hover: 0 20px 40px rgba(0, 0, 0, 0.15);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Inter', sans-serif;
        }

        body {
            background: var(--light-bg);
            color: var(--text-dark);
            line-height: 1.6;
            overflow-x: hidden;
        }

        .campana-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 20px;
        }

        /* Header Hero */
        .hero {
            background: var(--gradient);
            color: var(--white);
            padding: 100px 20px;
            text-align: center;
            position: relative;
            overflow: hidden;
            border-radius: 0 0 30px 30px;
            margin-bottom: 60px;
        }

        .hero::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1000 1000" opacity="0.1"><polygon fill="white" points="0,1000 1000,0 1000,1000"/></svg>');
            background-size: cover;
        }

        .hero-content {
            position: relative;
            z-index: 2;
            max-width: 800px;
            margin: 0 auto;
        }

        .logo {
            font-size: 80px;
            color: var(--accent);
            margin-bottom: 30px;
            animation: float 3s ease-in-out infinite;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }

        h1 {
            font-family: 'Playfair Display', serif;
            font-size: 4em;
            margin-bottom: 20px;
            background: linear-gradient(135deg, var(--accent), var(--accent-dark));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .subtitle {
            font-size: 1.6em;
            margin-bottom: 30px;
            opacity: 0.9;
            font-weight: 300;
        }

        .hero-description {
            font-size: 1.2em;
            margin-bottom: 40px;
            opacity: 0.8;
            max-width: 600px;
            margin-left: auto;
            margin-right: auto;
        }

        /* Hero Image Slider */
        .hero-slider {
            width: 100%;
            max-width: 800px;
            height: 400px;
            margin: 40px auto 0;
            border-radius: 20px;
            overflow: hidden;
            position: relative;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
        }

        .slide {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            opacity: 0;
            transition: opacity 1s ease-in-out;
            background-size: cover;
            background-position: center;
        }

        .slide.active {
            opacity: 1;
        }

        .slide-1 {
            background-image: url('https://images.unsplash.com/photo-1534438327276-14e5300c3a48?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=2070&q=80');
        }

        .slide-2 {
            background-image: url('https://images.unsplash.com/photo-1546069901-ba9599a7e63c?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=2080&q=80');
        }

        .slide-3 {
            background-image: url('https://images.unsplash.com/photo-1571019613454-1cb2f99b2d8b?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=2070&q=80');
        }

        /* Secciones */
        .section {
            background: var(--white);
            padding: 80px 50px;
            border-radius: 25px;
            margin-bottom: 60px;
            box-shadow: var(--shadow);
            transition: transform 0.3s ease;
        }

        .section:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-hover);
        }

        h2 {
            font-family: 'Playfair Display', serif;
            font-size: 3em;
            color: var(--text-dark);
            margin-bottom: 40px;
            text-align: center;
            position: relative;
        }

        h2::after {
            content: '';
            position: absolute;
            bottom: -15px;
            left: 50%;
            transform: translateX(-50%);
            width: 80px;
            height: 4px;
            background: var(--gradient-accent);
            border-radius: 2px;
        }

        h3 {
            font-size: 1.8em;
            color: var(--secondary);
            margin-bottom: 20px;
            font-weight: 600;
        }

        p {
            font-size: 1.1em;
            margin-bottom: 25px;
            color: var(--text-light);
            line-height: 1.8;
        }

        /* Features Grid */
        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 40px;
            margin: 60px 0;
        }

        .feature-card {
            background: var(--white);
            padding: 40px 30px;
            border-radius: 20px;
            text-align: center;
            transition: all 0.3s ease;
            border: 2px solid transparent;
            position: relative;
            overflow: hidden;
        }

        .feature-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 5px;
            background: var(--gradient-accent);
        }

        .feature-card:hover {
            transform: translateY(-10px);
            border-color: var(--accent);
            box-shadow: var(--shadow-hover);
        }

        .feature-image {
            width: 100%;
            height: 200px;
            border-radius: 15px;
            margin-bottom: 25px;
            object-fit: cover;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }

        .feature-card h3 {
            color: var(--secondary);
            margin-bottom: 15px;
        }

        .feature-card p {
            color: var(--text-light);
            font-size: 1em;
        }

        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 30px;
            margin: 50px 0;
        }

        .stat-card {
            background: var(--gradient);
            color: var(--white);
            padding: 40px 20px;
            border-radius: 20px;
            text-align: center;
            transition: transform 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: linear-gradient(45deg, transparent, rgba(255,255,255,0.1), transparent);
            transform: rotate(45deg);
            transition: all 0.6s ease;
        }

        .stat-card:hover::before {
            left: 100%;
        }

        .stat-card:hover {
            transform: scale(1.05);
        }

        .stat-number {
            font-size: 3.5em;
            font-weight: 700;
            margin-bottom: 10px;
            color: var(--accent);
        }

        .stat-card p {
            color: var(--white);
            opacity: 0.9;
            font-size: 1.1em;
            margin: 0;
        }

        /* Testimonials */
        .testimonials-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
            gap: 30px;
            margin: 50px 0;
        }

        .testimonial {
            background: var(--gradient);
            color: var(--white);
            padding: 40px;
            border-radius: 20px;
            position: relative;
            transition: transform 0.3s ease;
            display: flex;
            flex-direction: column;
        }

        .testimonial:hover {
            transform: translateY(-5px);
        }

        .testimonial::before {
            content: '"';
            position: absolute;
            top: 20px;
            left: 30px;
            font-size: 80px;
            color: var(--accent);
            opacity: 0.3;
            font-family: serif;
        }

        .testimonial-text {
            font-size: 1.2em;
            font-style: italic;
            margin-bottom: 25px;
            position: relative;
            z-index: 2;
            line-height: 1.7;
        }

        .author {
            display: flex;
            align-items: center;
            margin-top: auto;
        }

        .author-img {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            margin-right: 15px;
            object-fit: cover;
            border: 3px solid var(--accent);
        }

        .author-info {
            display: flex;
            flex-direction: column;
        }

        .author-name {
            font-weight: 600;
            font-size: 1.1em;
            color: var(--accent);
        }

        .author-role {
            font-size: 0.9em;
            opacity: 0.8;
        }

        /* Social Section */
        .social-section {
            text-align: center;
            background: var(--gradient);
            color: var(--white);
        }

        .social-section h2 {
            color: var(--white);
        }

        .social-section h2::after {
            background: var(--accent);
        }

        .social-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 30px;
            margin: 50px 0;
        }

        .social-card {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            padding: 40px 30px;
            border-radius: 20px;
            text-align: center;
            transition: all 0.3s ease;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .social-card:hover {
            transform: translateY(-10px);
            background: rgba(255, 255, 255, 0.15);
            border-color: var(--accent);
        }

        .social-icon {
            font-size: 4em;
            margin-bottom: 20px;
        }

        .social-card h3 {
            color: var(--white);
            margin-bottom: 15px;
        }

        .social-card p {
            color: rgba(255, 255, 255, 0.8);
            margin-bottom: 25px;
        }

        .instagram { color: #E4405F; }
        .facebook { color: #1877F2; }
        .tiktok { color: #000000; }
        .youtube { color: #FF0000; }

        /* CTA Section */
        .cta-section {
            text-align: center;
            padding: 100px 50px;
            background: var(--gradient);
            color: var(--white);
            border-radius: 30px;
            position: relative;
            overflow: hidden;
        }

        .cta-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1000 1000" opacity="0.05"><circle fill="white" cx="500" cy="500" r="400"/></svg>');
            background-size: cover;
        }

        .cta-section h2 {
            color: var(--white);
            position: relative;
            z-index: 2;
        }

        .cta-section p {
            color: rgba(255, 255, 255, 0.9);
            font-size: 1.3em;
            max-width: 700px;
            margin: 0 auto 50px;
            position: relative;
            z-index: 2;
        }

        .cta-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 25px;
            margin: 50px 0;
            position: relative;
            z-index: 2;
        }

        .cta-button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            padding: 20px 35px;
            background: var(--gradient-accent);
            color: var(--text-dark);
            text-decoration: none;
            border-radius: 50px;
            font-size: 1.2em;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 10px 30px rgba(255, 215, 0, 0.3);
            border: none;
            cursor: pointer;
        }

        .cta-button:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 40px rgba(255, 215, 0, 0.4);
            background: linear-gradient(135deg, #FFB347, #FFD700);
        }

        .cta-button.secondary {
            background: rgba(255, 255, 255, 0.2);
            color: var(--white);
            backdrop-filter: blur(10px);
            border: 2px solid rgba(255, 255, 255, 0.3);
        }

        .cta-button.secondary:hover {
            background: rgba(255, 255, 255, 0.3);
            border-color: var(--accent);
        }

        /* Navigation */
        .navigation {
            text-align: center;
            margin: 60px 0 40px;
        }

        .nav-button {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 16px 32px;
            background: var(--gradient);
            color: var(--white);
            text-decoration: none;
            border-radius: 25px;
            margin: 0 15px;
            transition: all 0.3s ease;
            font-weight: 500;
            box-shadow: var(--shadow);
        }

        .nav-button:hover {
            transform: translateY(-3px);
            box-shadow: var(--shadow-hover);
            background: var(--secondary);
        }

        /* Footer */
        .footer {
            text-align: center;
            padding: 40px 20px;
            background: var(--gradient);
            color: var(--white);
            border-radius: 30px 30px 0 0;
            margin-top: 80px;
        }

        .footer p {
            color: rgba(255, 255, 255, 0.8);
            margin: 0;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .hero {
                padding: 60px 20px;
            }

            .hero-slider {
                height: 300px;
            }

            .logo {
                font-size: 60px;
            }

            h1 {
                font-size: 2.8em;
            }

            h2 {
                font-size: 2.2em;
            }

            .section {
                padding: 50px 25px;
            }

            .features-grid {
                grid-template-columns: 1fr;
                gap: 25px;
            }

            .testimonials-grid {
                grid-template-columns: 1fr;
            }

            .cta-section {
                padding: 60px 25px;
            }

            .cta-grid {
                grid-template-columns: 1fr;
            }

            .nav-button {
                display: block;
                margin: 10px auto;
                max-width: 250px;
            }
        }

        @media (max-width: 480px) {
            h1 {
                font-size: 2.2em;
            }

            h2 {
                font-size: 1.8em;
            }

            .subtitle {
                font-size: 1.3em;
            }

            .section {
                padding: 40px 20px;
            }

            .hero-slider {
                height: 200px;
            }
        }
    </style>
</head>
<body>
    <div class="campana-container">
        <!-- Hero Section -->
        <section class="hero">
            <div class="hero-content">
                <div class="logo">
                    <i class="fas fa-dumbbell"></i>
                </div>
                <h1>GM Fitness & Nutrition</h1>
                <p class="subtitle">Transforma tu salud con la potencia de la inteligencia artificial</p>
                <p class="hero-description">Únete a la revolución del fitness tecnológico donde la ciencia se encuentra con la nutrición inteligente</p>
                <a href="register.php" class="cta-button">
                    <i class="fas fa-rocket"></i> Comenzar Ahora
                </a>
            </div>
            
            <!-- Hero Image Slider -->
            <div class="hero-slider">
                <div class="slide slide-1 active"></div>
                <div class="slide slide-2"></div>
                <div class="slide slide-3"></div>
            </div>
        </section>

        <!-- Presentación de la Campaña -->
        <section class="section">
            <h2>Nuestra Revolución en Redes Sociales</h2>
            <p>En GM Fitness & Nutrition creemos que la tecnología debe servir para mejorar nuestra salud y bienestar. Nuestra campaña "Salud Inteligente" busca democratizar el acceso a herramientas nutricionales avanzadas, llevando el poder de la IA a tu día a día.</p>
            
            <div class="features-grid">
                <div class="feature-card">
                    <img src="https://images.unsplash.com/photo-1571019613454-1cb2f99b2d8b?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=2070&q=80" alt="Análisis con IA" class="feature-image">
                    <h3>Análisis con IA</h3>
                    <p>Toma foto de tu comida y obtén análisis nutricional instantáneo con precisión del 95%</p>
                </div>
                <div class="feature-card">
                    <img src="https://images.unsplash.com/photo-1571019614242-c5c5dee9f50b?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=2070&q=80" alt="Seguimiento Precise" class="feature-image">
                    <h3>Seguimiento Precise</h3>
                    <p>Controla calorías, proteínas, carbohidratos y tu progreso en tiempo real</p>
                </div>
                <div class="feature-card">
                    <img src="https://images.unsplash.com/photo-1559757148-5c350d0d3c56?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=2070&q=80" alt="Tecnología Avanzada" class="feature-image">
                    <h3>Tecnología Avanzada</h3>
                    <p>Impulsado por Gemini AI de Google para máxima precisión y resultados</p>
                </div>
            </div>
        </section>

        <!-- Estadísticas -->
        <section class="section">
            <h2>Resultados que Hablan por Sí Solos</h2>
            <p>Nuestra plataforma ha transformado la vida de miles de usuarios con tecnología de vanguardia y resultados comprobados.</p>
            
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-number">95%</div>
                    <p>Precisión en análisis nutricional</p>
                </div>
                <div class="stat-card">
                    <div class="stat-number">2,500+</div>
                    <p>Usuarios satisfechos</p>
                </div>
                <div class="stat-card">
                    <div class="stat-number">15,000+</div>
                    <p>Comidas analizadas</p>
                </div>
                <div class="stat-card">
                    <div class="stat-number">24/7</div>
                    <p>Disponibilidad del sistema</p>
                </div>
            </div>
        </section>

        <!-- Testimonios -->
        <section class="section">
            <h2>Historias de Éxito Real</h2>
            <p>Descubre cómo nuestra comunidad está logrando resultados extraordinarios con GM Fitness & Nutrition.</p>
            
            <div class="testimonials-grid">
                <div class="testimonial">
                    <p class="testimonial-text">"Perdí 12kg en 4 meses usando GM Nutrition. La IA me ayudó a entender exactamente lo que como sin dietas extremas. ¡La foto de la comida y listo!"</p>
                    <div class="author">
                        <img src="https://images.unsplash.com/photo-1494790108755-2616b612b786?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1887&q=80" alt="Ana Rodríguez" class="author-img">
                        <div class="author-info">
                            <span class="author-name">Ana Rodríguez</span>
                            <span class="author-role">Cliente satisfecha</span>
                        </div>
                    </div>
                </div>
                <div class="testimonial">
                    <p class="testimonial-text">"Como deportista, necesitaba control preciso de proteínas. GM me da datos exactos en segundos. Increíble herramienta para atletas serios."</p>
                    <div class="author">
                        <img src="https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1887&q=80" alt="Carlos Méndez" class="author-img">
                        <div class="author-info">
                            <span class="author-name">Carlos Méndez</span>
                            <span class="author-role">Entrenador Personal</span>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Redes Sociales -->
        <section class="section social-section">
            <h2>Síguenos en Redes Sociales</h2>
            <p>Únete a nuestra comunidad y mantente actualizado con los últimos tips de nutrición, novedades tecnológicas y casos de éxito.</p>
            
            <div class="social-grid">
                <div class="social-card">
                    <div class="social-icon instagram">
                        <i class="fab fa-instagram"></i>
                    </div>
                    <h3>Instagram</h3>
                    <p>@GMFitnessNutrition</p>
                    <a href="https://www.instagram.com/gmfitnessnutrition?igsh=MWlveHBnbDh1OWFkaA%3D%3D&utm_source=qr" class="cta-button secondary" target="_blank">
                        <i class="fab fa-instagram"></i> Seguir
                    </a>
                </div>
                
                <div class="social-card">
                    <div class="social-icon tiktok">
                        <i class="fab fa-tiktok"></i>
                    </div>
                    <h3>TikTok</h3>
                    <p>@GMFitness</p>
                    <a href="https://www.tiktok.com/@gmfitnessnutrition?_r=1&_t=ZS-91TvWp6iiY8" class="cta-button secondary" target="_blank">
                        <i class="fab fa-tiktok"></i> Seguir
                    </a>
                </div>
                <div class="social-card">
                    <div class="social-icon youtube">
                        <i class="fab fa-youtube"></i>
                    </div>
                    <h3>YouTube</h3>
                    <p>GM Fitness Channel</p>
                    <a href="https://youtube.com/@gmfitnessnutrition?si=GqiMFIBe5w1NUMB9" class="cta-button secondary" target="_blank">
                        <i class="fab fa-youtube"></i> Suscribirse
                    </a>
                </div>
            </div>
        </section>

        <!-- Llamadas a la Acción -->
        <section class="cta-section">
            <h2>Comienza Tu Transformación Hoy</h2>
            <p>Únete a miles de usuarios que ya están transformando su salud con tecnología de vanguardia. Tu viaje hacia una vida más saludable comienza aquí.</p>
            
            <div class="cta-grid">
                <a href="register.php" class="cta-button">
                    <i class="fas fa-user-plus"></i> Regístrate Gratis
                </a>
                <a href="alimentos.php" class="cta-button secondary">
                    <i class="fas fa-camera"></i> Probar Análisis con IA
                </a>
                <a href="index.php" class="cta-button secondary">
                    <i class="fas fa-play-circle"></i> Ver Demo
                </a>
                <a href="#contact" class="cta-button secondary">
                    <i class="fas fa-envelope"></i> Contáctanos
                </a>
            </div>
        </section>

        <!-- Navegación -->
        <div class="navigation">
            <a href="index.php" class="nav-button">
                <i class="fas fa-home"></i> Volver al Sistema
            </a>
            <a href="dashboard.php" class="nav-button">
                <i class="fas fa-tachometer-alt"></i> Mi Dashboard
            </a>
        </div>

        <!-- Footer -->
        <footer class="footer">
            <p>&copy; 2024 GM Fitness & Nutrition. Todos los derechos reservados.</p>
        </footer>
    </div>

    <script>
        // Hero Slider
        document.addEventListener('DOMContentLoaded', function() {
            const slides = document.querySelectorAll('.slide');
            let currentSlide = 0;
            
            function showSlide(n) {
                slides.forEach(slide => slide.classList.remove('active'));
                currentSlide = (n + slides.length) % slides.length;
                slides[currentSlide].classList.add('active');
            }
            
            function nextSlide() {
                showSlide(currentSlide + 1);
            }
            
            // Cambiar slide cada 5 segundos
            setInterval(nextSlide, 5000);
            
            // Inicializar el primer slide
            showSlide(0);
        });
    </script>
</body>
</html>