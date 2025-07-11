<?php
// index.php - Page d'accueil dynamique Snapcat

session_start(); // Démarre la session

// --- DÉBOGAGE TEMPORAIRE : À RETIRER EN PRODUCTION ---
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);
// -----------------------------------------------------

// Inclut le fichier de connexion à la base de données.
require_once 'bd.php'; // Assurez-vous que c'est bien 'bd.php' ou 'db.php'

// Vérifie si l'objet de connexion PDO est valide après l'inclusion de bd.php
if (!isset($conn) || !$conn instanceof PDO) {
    die("Erreur fatale : La connexion à la base de données n'a pas pu être établie. Vérifiez votre fichier bd.php.");
}

require_once 'ChatModel.php'; // Inclure le ChatModel

try {
    $chatModel = new ChatModel($conn);
} catch (Exception $e) {
    error_log("Erreur critique lors de l'instanciation de ChatModel: " . $e->getMessage());
    // Gérer l'erreur de manière plus élégante en production (ex: afficher un message d'erreur générique)
    die("Désolé, une erreur est survenue lors du chargement des données. Veuillez réessayer plus tard.");
}

// --- LOGIQUE POUR LE CARROUSEL DE DÉCOUVERTE ---
//$cats = [];
//try {
    // Si l'utilisateur est connecté, on pourrait vouloir lui montrer des chats pertinents
    // ou tous les chats SAUF les siens. Pour l'instant, on prend tous les chats.
    // Adaptez cette requête selon votre logique de "découverte".
  //  $cats = $chatModel->getAllCatsForCarousel(null); // Récupère tous les chats

//} catch (Exception $e) {
  //  error_log("Erreur lors du chargement des chats pour le carrousel: " . $e->getMessage());
    // On ne fait pas mourir le script, mais on laisse $cats vide
//}


$message = ''; // Variable pour stocker les messages de succès ou d'erreur
$message_type = ''; // 'success' ou 'error' ou 'info'

// Gérer les messages de redirection (après ajout, modification, connexion, inscription, déconnexion)
if (isset($_GET['status'])) {
    if ($_GET['status'] === 'registration_success') {
        $message = 'Votre compte a été créé avec succès ! Veuillez vous connecter.';
        $message_type = 'success';
    } else if ($_GET['status'] === 'login_required') {
        $message = 'Veuillez vous connecter pour accéder à cette page.';
        $message_type = 'error';
    } else if ($_GET['status'] === 'logout_success') {
        $message = 'Vous avez été déconnecté avec succès.';
        $message_type = 'success';
    } else if ($_GET['status'] === 'success_update') {
        $cat_name = htmlspecialchars($_GET['cat_name'] ?? 'un chat');
        $message = 'Profil de ' . $cat_name . ' mis à jour avec succès !';
        $message_type = 'success';
    } else if ($_GET['status'] === 'success_add') {
        $message = 'Nouveau chat et snap ajoutés avec succès !';
        $message_type = 'success';
    }
}

// Simuler l'action "j'aime" ou "je n'aime pas" pour le carrousel
// Cette logique est copiée/adaptée de discover.php
if ($_SERVER['REQUEST_METHOD'] === 'POST' && (isset($_POST['like']) || isset($_POST['dislike']))) {
    // Vérifier si l'utilisateur est connecté pour permettre l'interaction
    if (!isset($_SESSION['user_id'])) {
        $message = "Vous devez être connecté pour interagir avec les profils.";
        $message_type = 'error';
    } else {
        $chat_id_interact = filter_input(INPUT_POST, 'chat_id', FILTER_SANITIZE_NUMBER_INT);
        if ($chat_id_interact) {
            // Dans une application réelle, vous stockeriez ces actions dans une table `user_interactions`
            // Ou simplement pour l'affichage :
            $chat_name_obj = $chatModel->getCatProfileById((int)$chat_id_interact, 0); // User_id 0 pour ne pas filtrer par propriétaire
            $chat_name_interact = htmlspecialchars($chat_name_obj['nom'] ?? 'ce chat');


            if (isset($_POST['like'])) {
                $message = "Vous avez aimé " . $chat_name_interact . " !";
                $message_type = 'success';
            } else { // dislike
                $message = "Vous n'avez pas aimé " . $chat_name_interact . ".";
                $message_type = 'info'; // Nouveau type pour "info"
            }
        }
    }
}

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Snapcat - Clinique Vétérinaire Fictive pour Chats !</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f8f8f8; /* Light gray for the body background */
            color: #333;
            line-height: 1.6;
            padding: 0;
            margin: 0;
            overflow-x: hidden; /* Prevent horizontal scroll */
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 15px;
        }
        /* Header */
        .header-top {
            background-color: #007bff; /* Blue */
            color: white;
            padding: 10px 0;
            font-size: 0.9em;
        }
        .header-main {
            background-color: #fff;
            padding: 20px 0;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        }
        .logo {
            font-size: 2.2rem;
            font-weight: 800;
            color: #007bff;
            text-decoration: none;
        }
        .logo span {
            color: #28a745; /* Green */
        }
        .main-nav a {
            color: #333;
            text-decoration: none;
            padding: 10px 15px;
            font-weight: 600;
            transition: color 0.3s ease;
        }
        .main-nav a:hover {
            color: #007bff;
        }
        .btn-cta {
            background-color: #28a745; /* Green */
            color: white;
            padding: 10px 20px;
            border-radius: 5px;
            text-decoration: none;
            font-weight: 600;
            transition: background-color 0.3s ease;
        }
        .btn-cta:hover {
            background-color: #218838;
        }

        /* Hero Section */
        .hero-section {
            background: linear-gradient(rgba(0,0,0,0.6), rgba(0,0,0,0.6)), url('https://placehold.co/1920x600/336699/FFFFFF/webp?text=Hero+Background') no-repeat center center/cover;
            color: white;
            padding: 100px 0;
            text-align: center;
        }
        .hero-section h1 {
            font-size: 3.5rem;
            font-weight: 800;
            margin-bottom: 20px;
        }
        .hero-section p {
            font-size: 1.25rem;
            margin-bottom: 40px;
            max-width: 700px;
            margin-left: auto;
            margin-right: auto;
        }

        /* General Section Styling */
        .section-padding {
            padding: 80px 0;
        }
        .section-heading {
            text-align: center;
            margin-bottom: 60px;
            font-size: 2.5rem;
            font-weight: 700;
            color: #007bff;
        }
        .section-heading span {
            color: #28a745;
        }

        /* About Section */
        .about-us-img {
            border-radius: 8px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        /* Services Section */
        .service-card {
            background-color: #fff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.05);
            text-align: center;
            transition: transform 0.3s ease;
        }
        .service-card:hover {
            transform: translateY(-5px);
        }
        .service-card i {
            font-size: 3rem;
            color: #007bff;
            margin-bottom: 20px;
        }
        .service-card h3 {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 15px;
            color: #333;
        }

        /* Team Section */
        .team-member-card {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.05);
            text-align: center;
        }
        .team-member-card img {
            width: 150px;
            height: 150px;
            object-fit: cover;
            border-radius: 50%;
            border: 4px solid #007bff;
            margin: 0 auto 20px;
        }
        .team-member-card h3 {
            font-size: 1.4rem;
            font-weight: 700;
            color: #333;
        }
        .team-member-card p {
            color: #666;
            font-size: 0.9em;
        }

        /* Testimonials Section */
        .testimonial-card {
            background-color: #f0f8ff; /* Light blue background */
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.05);
            margin-bottom: 30px;
        }
        .testimonial-card p {
            font-style: italic;
            margin-bottom: 15px;
            color: #4a5568;
        }
        .testimonial-card .author {
            font-weight: 600;
            color: #007bff;
        }

        /* Call to Action */
        .cta-section {
            background-color: #007bff;
            color: white;
            padding: 60px 0;
            text-align: center;
        }
        .cta-section h2 {
            color: white;
            margin-bottom: 20px;
        }

        /* Footer */
        .footer {
            background-color: #333;
            color: white;
            padding: 40px 0;
            font-size: 0.9em;
        }
        .footer-logo {
            font-size: 1.8rem;
            font-weight: 800;
            color: white;
            text-decoration: none;
            margin-bottom: 20px;
            display: block;
        }
        .footer-logo span {
            color: #28a745;
        }
        .footer-links a {
            color: white;
            text-decoration: none;
            margin-right: 15px;
            transition: color 0.3s ease;
        }
        .footer-links a:hover {
            color: #007bff;
        }
        .social-icons a {
            color: white;
            font-size: 1.5rem;
            margin-right: 15px;
            transition: color 0.3s ease;
        }
        .social-icons a:hover {
            color: #007bff;
        }

        /* Carrousel specific styles (adapted from discover.php) */
        .carousel-section {
            background-color: #f0f8ff; /* Light blue background */
            padding: 60px 0;
            text-align: center;
        }
        .carousel-section h2 {
            color: #007bff;
        }
        .carousel-container {
            position: relative;
            width: 100%;
            max-width: 450px; /* Adjust max width for carousel */
            height: 450px; /* Adjust height for carousel */
            margin: 0 auto 30px;
            overflow: hidden;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
            background-color: #fff;
        }
        .carousel-item {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            background-color: #fff;
            transition: opacity 0.5s ease-in-out;
            opacity: 0;
            visibility: hidden;
            padding: 20px;
            box-sizing: border-box;
        }
        .carousel-item.active {
            opacity: 1;
            visibility: visible;
        }
        .carousel-item img {
            max-width: 90%;
            max-height: 200px; /* Adjust image height */
            object-fit: contain; /* Changed to contain to avoid cropping */
            border-radius: 8px;
            margin-bottom: 15px;
        }
        .carousel-item h3 {
            margin-bottom: 5px;
            color: #333;
            font-size: 1.5em;
        }
        .carousel-item p {
            font-size: 0.95em;
            color: #666;
            margin: 2px 0;
            text-align: center;
        }
        .carousel-controls {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin-top: 20px;
        }
        .carousel-controls button {
            padding: 12px 25px;
            border: none;
            border-radius: 30px;
            cursor: pointer;
            font-size: 1.1em;
            font-weight: bold;
            transition: background-color 0.3s ease;
            color: white;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .like-btn { background-color: #28a745; }
        .like-btn:hover { background-color: #218838; }
        .dislike-btn { background-color: #dc3545; }
        .dislike-btn:hover { background-color: #c82333; }

        .message-box {
            padding: 1rem;
            border-radius: 0.5rem;
            margin-bottom: 1.5rem;
            border-width: 1px;
            font-weight: 500;
            text-align: center;
            max-width: 500px;
            margin-left: auto;
            margin-right: auto;
        }
        .message-success {
            background-color: #d1fae5;
            border-color: #34d399;
            color: #065f46;
        }
        .message-error {
            background-color: #fee2e2;
            border-color: #ef4444;
            color: #b91c1c;
        }
        .message-info { /* New style for 'info' messages */
            background-color: #e0f2fe; /* light blue */
            border-color: #38bdf8; /* sky blue */
            color: #0c4a6e; /* dark blue */
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .header-main .flex {
                flex-direction: column;
                text-align: center;
            }
            .main-nav {
                margin-top: 15px;
                flex-wrap: wrap;
                justify-content: center;
            }
            .hero-section h1 {
                font-size: 2.5rem;
            }
            .hero-section p {
                font-size: 1rem;
            }
            .section-heading {
                font-size: 2rem;
            }
            .about-us-content, .about-us-img {
                flex-basis: 100% !important; /* Force single column */
            }
            .service-card, .team-member-card, .testimonial-card {
                margin-bottom: 30px;
            }
            .footer .flex {
                flex-direction: column;
                text-align: center;
            }
            .footer-links, .social-icons {
                margin-top: 15px;
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <header>
        <div class="header-top">
            <div class="container flex justify-between items-center py-2">
                <div class="flex items-center space-x-4">
                    <span><i class="fas fa-phone-alt"></i> +33 123 456 789</span>
                    <span><i class="fas fa-envelope"></i> contact@snapcat.com</span>
                </div>
                <div>
                    </div>
            </div>
        </div>
        <div class="header-main">
            <div class="container flex justify-between items-center">
                <a href="index.php" class="logo">Snap<span>Cat</span></a>
                <nav class="main-nav hidden md:flex space-x-6">
                    <a href="#hero">Accueil</a>
                    <a href="#about">À Propos</a>
                    <a href="#discover">Découverte</a>
                    <a href="#services">Services</a>
                    <a href="#team">Notre Équipe</a>
                    <a href="#contact">Contact</a>
                </nav>
                <div class="flex items-center space-x-4">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <a href="main.php" class="btn-cta">Mon Dashboard</a>
                        <a href="logout.php" class="btn-cta bg-red-500 hover:bg-red-600">Déconnexion</a>
                    <?php else: ?>
                        <a href="login.php" class="btn-cta">Connexion</a>
                        <a href="register.php" class="btn-cta bg-gray-600 hover:bg-gray-700">Inscription</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </header>

    <section id="hero" class="hero-section">
        <div class="container">
            <h1>Bienvenue à la Clinique - Bordel des Amis des chats  <span>Snapcat</span></h1>
            <p>
                Prenez soin de vos compagnons félins avec passion et expertise.
                Découvrez des profils de chats uniques et partagez la joie de la vie féline !
            </p>
            <a href="#discover" class="btn-cta inline-block mt-4 text-lg">Découvrir les Chats</a>
        </div>
    </section>

    <?php if ($message): ?>
        <div class="container mt-8">
            <div class="message-box <?php echo ($message_type === 'success') ? 'message-success' : (($message_type === 'error') ? 'message-error' : 'message-info'); ?>">
                <span class="block sm:inline"><?php echo htmlspecialchars($message); ?></span>
            </div>
        </div>
    <?php endif; ?>

    <section id="about" class="section-padding bg-white">
        <div class="container flex flex-col md:flex-row items-center gap-10">
            <div class="md:w-1/2">
                <img src="https://images.pushsquare.com/734bbdbaccad1/1280x720.jpg" alt="Un chat curieux observant" class="about-us-img w-full h-auto">
            </div>
            <div class="md:w-1/2 text-left">
                <h2 class="section-heading text-left">À Propos de <span>Nous</span></h2>
                <p class="text-lg text-gray-700 mb-4">
                    Chez Snapcat, nous combinons l'amour des animaux avec la technologie pour créer une communauté unique. Au-delà d'une simple plateforme de partage, nous sommes un hub pour tous les amoureux des chats, offrant des outils pour gérer les profils de vos félins et interagir avec d'autres passionnés.
                </p>
                <p class="text-gray-600">
                    Notre mission est de faciliter la découverte et le partage autour de la vie fascinante de nos compagnons à quatre pattes. Que ce soit pour trouver un nouveau compagnon, partager les dernières facéties de votre chat, ou simplement admirer les beautés félines du monde entier, Snapcat est là pour vous.
                </p>
                <a href="#contact" class="btn-cta inline-block mt-6">Nous Contacter</a>
            </div>
        </div>
    </section>

    <section id="discover" class="carousel-section">
        <div class="container">
            <h2 class="section-heading text-white mb-8">Découvrez de nouveaux amis <span>félins</span> !</h2>

            <?php if (!empty($cats)): ?>
                <div class="carousel-container">
                    <?php foreach ($cats as $index => $chat): ?>
                        <div class="carousel-item <?php echo ($index === 0) ? 'active' : ''; ?>" data-chat-id="<?php echo htmlspecialchars($chat['id']); ?>">
                            <?php if (!empty($chat['photo'])): ?>
                                <img src="<?php echo htmlspecialchars($chat['photo']); ?>" alt="Photo de <?php echo htmlspecialchars($chat['nom']); ?>">
                            <?php else: ?>
                                <img src="https://placehold.co/150x150/e2e8f0/64748b?text=Pas+de+Photo" alt="Pas de Photo disponible" class="w-24 h-24 object-cover rounded-full">
                            <?php endif; ?>
                            <h3><?php echo htmlspecialchars($chat['nom']); ?></h3>
                            <p><strong>Âge :</strong> <?php echo htmlspecialchars($chat['age'] ?? 'N/A'); ?> ans</p>
                            <p><strong>Sexe :</strong> <?php echo htmlspecialchars($chat['sexe'] ?? 'N/A'); ?></p>
                            <p><strong>Localisation :</strong> <?php echo htmlspecialchars($chat['localisation'] ?? 'N/A'); ?></p>
                            <p><strong>Description :</strong> <?php echo nl2br(htmlspecialchars($chat['description'] ?? 'N/A')); ?></p>
                            <p><strong>Intérêts :</strong> <?php echo htmlspecialchars(implode(', ', $chat['interets'] ?? [])); ?></p>
                        </div>
                    <?php endforeach; ?>
                </div>

                <?php if (isset($_SESSION['user_id'])): // Afficher les contrôles seulement si connecté ?>
                    <div class="carousel-controls">
                        <form action="index.php#discover" method="POST" style="display: inline;" class="interact-form">
                            <input type="hidden" name="chat_id" class="current-chat-id">
                            <button type="submit" name="dislike" class="dislike-btn"><i class="fas fa-times"></i> Passer</button>
                        </form>
                        <form action="index.php#discover" method="POST" style="display: inline;" class="interact-form">
                            <input type="hidden" name="chat_id" class="current-chat-id">
                            <button type="submit" name="like" class="like-btn"><i class="fas fa-heart"></i> J'aime !</button>
                        </form>
                    </div>
                    <p class="text-gray-700 text-lg mt-6">
                        Envie d'ajouter votre propre compagnon ? <a href="add_cat.php" class="text-blue-700 font-bold hover:underline">Ajoutez un chat maintenant !</a>
                    </p>
                <?php else: ?>
                    <p class="text-gray-700 text-lg">
                        <a href="login.php" class="text-blue-700 font-bold hover:underline">Connectez-vous</a> ou
                        <a href="register.php" class="text-blue-700 font-bold hover:underline">inscrivez-vous</a> pour découvrir plus de chats, interagir, et ajouter le vôtre !
                    </p>
                <?php endif; ?>
            <?php else: ?>
                <p class="text-gray-700 text-lg">
                    Aucun profil de chat disponible pour la découverte pour le moment.
                    <br>
                    <?php if (isset($_SESSION['user_id'])): ?>
                        Soyez le premier ! <a href="add_cat.php" class="text-blue-700 font-bold hover:underline">Ajoutez votre chat maintenant !</a>
                    <?php else: ?>
                        <a href="register.php" class="text-blue-700 font-bold hover:underline">Créez un compte</a> pour commencer à ajouter des profils.
                    <?php endif; ?>
                </p>
            <?php endif; ?>
        </div>
    </section>

    <section id="services" class="section-padding bg-white">
        <div class="container">
            <h2 class="section-heading">Nos <span>Services</span></h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="service-card">
                    <i class="fas fa-paw"></i>
                    <h3>Profils Personnalisés</h3>
                    <p>Créez des fiches détaillées pour vos chats avec photos, descriptions et plus encore.</p>
                </div>
                <div class="service-card">
                    <i class="fas fa-camera"></i>
                    <h3>Snaps Quotidiens</h3>
                    <p>Partagez les moments drôles et mémorables de vos félins avec la communauté.</p>
                </div>
                <div class="service-card">
                    <i class="fas fa-search"></i>
                    <h3>Découverte de Chats</h3>
                    <p>Explorez une vaste galerie de profils et interagissez avec de nouveaux amis.</p>
                </div>
                <div class="service-card">
                    <i class="fas fa-heartbeat"></i>
                    <h3>Bien-être Félin</h3>
                    <p>Conseils et astuces pour la santé et le bonheur de vos compagnons.</p>
                </div>
                <div class="service-card">
                    <i class="fas fa-users"></i>
                    <h3>Communauté Active</h3>
                    <p>Rejoignez des milliers de passionnés de chats et échangez vos expériences.</p>
                </div>
                <div class="service-card">
                    <i class="fas fa-award"></i>
                    <h3>Événements et Concours</h3>
                    <p>Participez à nos événements spéciaux et gagnez des prix pour vos chats.</p>
                </div>
            </div>
        </div>
    </section>

    <section id="team" class="section-padding bg-gray-100">
        <div class="container">
            <h2 class="section-heading">Notre <span>Équipe</span></h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="team-member-card">
                    <img src="https://placehold.co/150x150/f0f0f0/333333/webp?text=John" alt="Portrait de Dr. John Doe, vétérinaire en chef" loading="lazy">
                    <h3>Dr. John Doe</h3>
                    <p>Vétérinaire Chef</p>
                    <p class="text-sm text-gray-600">Expert en comportement félin et nutrition.</p>
                </div>
                <div class="team-member-card">
                    <img src="https://placehold.co/150x150/f0f0f0/333333/webp?text=Jane" alt="Portrait de Dr. Jane Smith, développeuse en chef" loading="lazy">
                    <h3>Dr. Jane Smith</h3>
                    <p>Développeuse en chef</p>
                    <p class="text-sm text-gray-600">Architecte de la plateforme Snapcat.</p>
                </div>
                <div class="team-member-card">
                    <img src="https://placehold.co/150x150/f0f0f0/333333/webp?text=Mike" alt="Portrait de Mike Johnson, responsable communauté" loading="lazy">
                    <h3>Mike Johnson</h3>
                    <p>Responsable Communauté</p>
                    <p class="text-sm text-gray-600">Anime la communauté et organise les événements.</p>
                </div>
            </div>
        </div>
    </section>

    <section id="testimonials" class="section-padding bg-white">
        <div class="container">
            <h2 class="section-heading">Ce que disent nos <span>utilisateurs</span></h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <div class="testimonial-card">
                    <p>"Snapcat a transformé la façon dont je partage la vie de mon chat. L'interface est intuitive et la communauté est géniale !"</p>
                    <div class="author">- Marie D.</div>
                </div>
                <div class="testimonial-card">
                    <p>"J'ai découvert tellement de chats adorables ici ! La fonction de découverte est addictive et les profils sont très bien faits."</p>
                    <div class="author">- David L.</div>
                </div>
            </div>
        </div>
    </section>

    <section class="cta-section">
        <div class="container">
            <h2 class="section-heading text-white">Prêt à rejoindre la communauté Snapcat ?</h2>
            <p class="text-lg mb-8">Inscrivez-vous gratuitement et commencez à partager la joie avec d'autres amoureux des chats.</p>
            <a href="register.php" class="btn-cta inline-block text-xl">S'inscrire Maintenant !</a>
        </div>
    </section>

    <section id="contact" class="section-padding bg-gray-100">
        <div class="container">
            <h2 class="section-heading">Nous <span>Contacter</span></h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-10">
                <div>
                    <h3 class="text-2xl font-bold text-gray-800 mb-4">Informations de Contact</h3>
                    <p class="mb-2"><i class="fas fa-map-marker-alt text-blue-500 mr-2"></i> 272 route de launaguet</p>
                    <p class="mb-2"><i class="fas fa-phone-alt text-blue-500 mr-2"></i> +33 781 95 53 24</p>
                    <p class="mb-2"><i class="fas fa-envelope text-blue-500 mr-2"></i> contact@snapcat.com</p>
                    <p class="mb-4"><i class="fas fa-clock text-blue-500 mr-2"></i> Lun - Ven: 9h00 - 18h00</p>
                    <div class="map-placeholder bg-gray-300 h-64 rounded-lg overflow-hidden">
                        <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d2888.7525287955523!2d1.4295964155829916!3d43.64010897912111!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x12a969df2d2b5123%3A0x33e89c31b8d2a632!2s272%20Rte%20de%20Launaguet%2C%2031200%20Toulouse!5e0!3m2!1sfr!2sfr!4v1678912345678!5m2!1sfr!2sfr" width="100%" height="100%" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
                    </div>
                </div>
                <div>
                    <h3 class="text-2xl font-bold text-gray-800 mb-4">Envoyez-nous un message</h3>
                    <form action="#" method="POST" class="space-y-4">
                        <div>
                            <input type="text" placeholder="Votre Nom" class="w-full p-3 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <input type="email" placeholder="Votre Email" class="w-full p-3 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <input type="text" placeholder="Sujet" class="w-full p-3 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <textarea placeholder="Votre Message" rows="5" class="w-full p-3 border border-gray-300 rounded-md focus:outline-none focus:shadow-outline"></textarea>
                        </div>
                        <button type="submit" class="btn-cta w-full py-3">Envoyer le Message</button>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <footer class="footer">
        <div class="container flex flex-col md:flex-row justify-between items-center text-center md:text-left">
            <a href="index.php" class="footer-logo">Snap<span>Cat</span></a>
            <div class="footer-links flex flex-col md:flex-row mt-4 md:mt-0 space-y-2 md:space-y-0 md:space-x-4">
                <a href="#hero">Accueil</a>
                <a href="#about">À Propos</a>
                <a href="#services">Services</a>
                <a href="#team">Équipe</a>
                <a href="#contact">Contact</a>
            </div>
            <div class="social-icons mt-4 md:mt-0">
                <a href="#" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
                <a href="#" aria-label="Twitter"><i class="fab fa-twitter"></i></a>
                <a href="#" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
                <a href="#" aria-label="LinkedIn"><i class="fab fa-linkedin-in"></i></a>
            </div>
        </div>
        <div class="text-center mt-8 text-gray-400">
            © <?php echo date('Y'); ?> Snapcat. Tous droits réservés.
        </div>
    </footer>

    <script>
        const carouselItems = document.querySelectorAll('.carousel-item');
        const currentChatIdInputs = document.querySelectorAll('.current-chat-id');
        let currentIndex = 0;

        function showSlide(index) {
            carouselItems.forEach((item, i) => {
                if (i === index) {
                    item.classList.add('active');
                    // Assurez-vous de mettre à jour tous les inputs cachés pour les formulaires "like" et "dislike"
                    currentChatIdInputs.forEach(input => {
                        input.value = item.dataset.chatId;
                    });
                } else {
                    item.classList.remove('active');
                }
            });
        }

        // Fonction pour passer au chat suivant
        function nextSlide() {
            currentIndex = (currentIndex + 1) % carouselItems.length;
            showSlide(currentIndex);
        }

        // Ajoutez des écouteurs d'événements aux formulaires de "like" et "dislike"
        // pour avancer le carrousel après l'interaction (simulée ici)
        document.querySelectorAll('.interact-form').forEach(form => {
            form.addEventListener('submit', function(event) {
                // Empêche le rechargement de la page si vous voulez un comportement AJAX futur
                // event.preventDefault();

                // Simule l'avancement du carrousel après l'interaction
                if (carouselItems.length > 1) { // Avance seulement s'il y a plus d'un chat
                    // Petite astuce pour que le message s'affiche AVANT le changement de slide visuel
                    setTimeout(() => {
                        nextSlide();
                    }, 100); // Court délai pour laisser le temps au message de s'afficher

                    // Ici, si vous utilisiez AJAX, vous feriez votre requête puis appelleriez nextSlide dans le .then()
                }
                // Si la page se recharge (comportement par défaut), la logique PHP gérera le message et le carrousel sera réinitialisé au premier élément
            });
        });


        // Initialiser le carrousel au chargement
        if (carouselItems.length > 0) {
            showSlide(currentIndex);
        } else {
            console.log("Aucun élément dans le carrousel à afficher.");
            // Gérer visuellement l'absence d'éléments si nécessaire
            const carouselContainer = document.querySelector('.carousel-container');
            if (carouselContainer) {
                carouselContainer.innerHTML = '<p class="text-gray-700 text-lg p-5">Pas de chats à afficher pour le moment.</p>';
                carouselContainer.style.height = 'auto'; // Ajuste la hauteur si vide
                carouselContainer.style.boxShadow = 'none'; // Pas d'ombre si vide
                carouselContainer.style.backgroundColor = 'transparent'; // Pas de fond si vide
            }
        }
    </script>
</body>
</html>