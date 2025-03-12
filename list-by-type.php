<?php
// Configuration de la connexion à la base de données
$serveur = "localhost";
$utilisateur = "root";
$motdepasse = ""; // Généralement vide sur XAMPP/WAMP
$bdd = "pokemon";

// Connexion à la base de données
try {
    $connexion = new PDO("mysql:host=$serveur;dbname=$bdd", $utilisateur, $motdepasse);
    $connexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $connexion->exec("SET NAMES utf8");
} catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}

// Récupérer tous les types de Pokémon pour le formulaire
$requeteTypes = $connexion->query("SELECT * FROM type_pokemon ORDER BY nomType");
$types = $requeteTypes->fetchAll(PDO::FETCH_ASSOC);

// Vérifier si un type a été soumis
$resultatRecherche = [];
if (isset($_GET['type']) && !empty($_GET['type'])) {
    $idType = (int)$_GET['type'];
    
    // Requête pour trouver les Pokémon du type sélectionné (type primaire ou secondaire)
    $requete = $connexion->prepare("
        SELECT p.*, t1.nomType as typePrimaire, t2.nomType as typeSecondaire 
        FROM pokemon p
        INNER JOIN type_pokemon t1 ON p.idType1 = t1.idType
        LEFT JOIN type_pokemon t2 ON p.idType2 = t2.idType
        WHERE p.idType1 = :idType OR p.idType2 = :idType
        ORDER BY p.idPokemon
    ");
    $requete->bindParam(':idType', $idType, PDO::PARAM_INT);
    $requete->execute();
    $resultatRecherche = $requete->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recherche de Pokémon par Type</title>
    <style>
        :root {   
            --red-lighter: rgb(246, 49, 49);   
            --red-darker: rgb(183, 0, 0); 
        }  
        
        @font-face {   
            font-family: "Pokemon";   
            src: url("assets/Pokemon Solid.ttf"); 
        }  
        
        html, body {   
            margin: 0;   
            box-sizing: border-box;   
            font-family: "Gill Sans", "Gill Sans MT", Calibri, "Trebuchet MS", sans-serif;   
            -webkit-font-smoothing: antialiased;   
            background-color: rgb(246, 167, 167); 
        }  
        
        *:focus {   
            outline: none; 
        }  
        
        header {   
            background: rgb(255, 255, 255);   
            background: radial-gradient(     
                circle,     
                rgba(255, 255, 255, 1) 0%,     
                var(--red-darker) 10%,     
                var(--red-darker) 100%   
            );   
            border-bottom: 1px solid var(--red-darker); 
        } 
        
        header > a {   
            text-decoration: none; 
        }  
        
        header > a > h1 {   
            margin: 0.5rem;   
            text-align: center;   
            font-family: "Pokemon", Arial, Helvetica, sans-serif;   
            font-size: 3rem;   
            color: rgb(255, 191, 0);   
            -webkit-text-stroke: 0.2rem rgb(23, 23, 130);   
            text-shadow: 0 0px 12px white; 
        }  
        
        footer {   
            min-height: 2rem;   
            background-color: var(--red-lighter);   
            text-align: center;   
            color: white; 
        }  
        
        body {   
            display: flex;   
            flex-direction: column;   
            align-items: stretch;   
            justify-content: space-between;   
            height: 100vh; 
        } 
        
        #main-wrapper {   
            flex: 1 0 auto;   
            display: flex;   
            align-items: stretch; 
        }  
        
        #main-wrapper main {   
            flex: 1 0 auto; 
        } 
        
        #side-menu {   
            min-width: 15rem;   
            max-width: 25%;   
            background: rgb(255, 255, 255);   
            background: linear-gradient(     
                180deg,     
                var(--red-darker) 0%,     
                var(--red-lighter) 100%   
            ); 
        }  
        
        #side-menu a {   
            color: white; 
        }  
        
        #side-menu ul {   
            list-style-image: url("assets/pokeball.svg"); 
        }  
        
        article, footer {   
            padding: 0.5rem; 
        }  
        
        #search-bar {   
            padding: 0.5rem;   
            display: flex;   
            justify-content: end; 
        }  
        
        #search-bar input {   
            padding-left: 1rem; 
        }  
        
        #search-bar label {   
            color: white; 
        }  
        
        #search-bar > *:not(:last-child) {   
            margin-right: 0.5rem; 
        }  
        
        .input-group {   
            display: inline;   
            vertical-align: baseline; 
        }  
        
        .input-group > * {   
            line-height: 1.5rem;   
            border: none; 
        } 
        
        .input-group > *:first-child {   
            border-radius: 25px 25px 0% 25px / 25px 0% 25px 25px; 
        }  
        
        .input-group > *:last-child {   
            border-radius: 25px 25px 25px 25px / 0% 25px 25px 0%; 
        }  
        
        .text-center {   
            text-align: center; 
        }

        /* Styles originaux de la carte pokémon */
        .resultat {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
            margin-top: 30px;
        }
        
        .pokemon-card {
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 15px;
            text-align: center;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            background-color: white;
        }
        
        .pokemon-card img {
            max-width: 100px;
            height: auto;
        }
        
        .pokemon-card h3 {
            margin: 10px 0 5px;
            color: #2a75bb;
        }
        
        .pokemon-stats {
            margin-top: 10px;
            text-align: left;
            font-size: 14px;
        }
        
        .type {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 4px;
            margin: 2px;
            font-size: 12px;
            color: white;
            font-weight: bold;
        }
        
        .type-Plante { background-color: #78C850; }
        .type-Poison { background-color: #A040A0; }
        .type-Feu { background-color: #F08030; }
        .type-Eau { background-color: #6890F0; }
        .type-Insecte { background-color: #A8B820; }
        .type-Vol { background-color: #A890F0; }
        .type-Normal { background-color: #A8A878; }
        .type-Électrique, .type-Electrique { background-color: #F8D030; }
        .type-Sol { background-color: #E0C068; }
        .type-Combat { background-color: #C03028; }
        .type-Psy { background-color: #F85888; }
        .type-Roche { background-color: #B8A038; }
        .type-Glace { background-color: #98D8D8; }
        .type-Dragon { background-color: #7038F8; }
        .type-Spectre { background-color: #705898; }
        .type-Fée { background-color: #EE99AC; }
        .type-Ténèbres, .type-Tenebres { background-color: #705848; }
        .type-Acier { background-color: #B8B8D0; }
        
        .no-result {
            text-align: center;
            grid-column: 1 / -1;
            margin-top: 20px;
            font-style: italic;
            color: white;
        }
    </style>
</head>
<body>
    <header>
        <a href="index.php">
            <h1>Pokédex</h1>
        </a>
    </header>
    
    <div id="main-wrapper">
        <div id="side-menu">
            <div id="search-bar">
                <form method="GET" action="" class="input-group">
                    <select name="type" id="type">
                        <option value="">Sélectionnez un type</option>
                        <?php foreach ($types as $type): ?>
                            <option value="<?= $type['idType'] ?>" <?= (isset($_GET['type']) && $_GET['type'] == $type['idType']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($type['nomType']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <button type="submit">Rechercher</button>
                </form>
            </div>
            <ul>
                <li><a href="index.php">Accueil</a></li>
            </ul>
        </div>
        
        <main>
            <article>
                <h2 class="text-center">Recherche de Pokémon par Type</h2>
                
                <div class="resultat">
                    <?php if (isset($_GET['type']) && !empty($_GET['type'])): ?>
                        <?php if (count($resultatRecherche) > 0): ?>
                            <?php foreach ($resultatRecherche as $pokemon): ?>
                                <div class="pokemon-card">
                                    <?php if (!empty($pokemon['urlPhoto'])): ?>
                                        <img src="<?= htmlspecialchars($pokemon['urlPhoto']) ?>" alt="<?= htmlspecialchars($pokemon['nomPokemon']) ?>">
                                    <?php endif; ?>
                                    
                                    <h3>#<?= str_pad($pokemon['idPokemon'], 3, '0', STR_PAD_LEFT) ?> - <?= htmlspecialchars($pokemon['nomPokemon']) ?></h3>
                                    
                                    <div>
                                        <span class="type type-<?= htmlspecialchars($pokemon['typePrimaire']) ?>"><?= htmlspecialchars($pokemon['typePrimaire']) ?></span>
                                        <?php if (!empty($pokemon['typeSecondaire'])): ?>
                                            <span class="type type-<?= htmlspecialchars($pokemon['typeSecondaire']) ?>"><?= htmlspecialchars($pokemon['typeSecondaire']) ?></span>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <div class="pokemon-stats">
                                        <div>PV: <?= $pokemon['PV'] ?></div>
                                        <div>Attaque: <?= $pokemon['PtsAttaque'] ?></div>
                                        <div>Défense: <?= $pokemon['PtsDefense'] ?></div>
                                        <div>Vitesse: <?= $pokemon['PtsVitesse'] ?></div>
                                        <div>Spécial: <?= $pokemon['PtsSpecial'] ?></div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="no-result">Aucun Pokémon trouvé pour ce type.</div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </article>
        </main>
    </div>
    
    <footer>
        <p>© 2025 Pokédex - Tous droits réservés</p>
    </footer>
</body>
</html>