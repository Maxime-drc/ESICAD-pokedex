<?php
// Connexion à la base de données
$databaseConnection = mysqli_connect(
    'localhost',
    "root",
    "",
    "pokemon",
    "3306"
);
// Définir l'encodage des caractères
mysqli_set_charset($databaseConnection, "utf8mb4");
// Requête pour récupérer les pokémon avec leurs types
$query = "
    SELECT p.idPokemon, p.nomPokemon, p.urlPhoto, p.PV, p.PtsAttaque, p.PtsDefense, 
           p.PtsVitesse, p.PtsSpecial, p.DateAjout, 
           t1.nomType AS type1, t2.nomType AS type2
    FROM pokemon p
    INNER JOIN type_pokemon t1 ON p.idType1 = t1.idType
    LEFT JOIN type_pokemon t2 ON p.idType2 = t2.idType
    ORDER BY p.idPokemon
";
$result = mysqli_query($databaseConnection, $query);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pokédex</title>
    <style>
        :root {
          --red-lighter: rgb(246, 49, 49);
          --red-darker: rgb(183, 0, 0);
        }

        @font-face {
          font-family: "Pokemon";
          src: url("assets/Pokemon Solid.ttf");
        }

        html,
        body {
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

        article,
        footer {
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
        
        /* Styles pour la table pokémon */
        .pokemon-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background-color: white;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        .pokemon-table th, .pokemon-table td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        .pokemon-table th {
            background-color: var(--red-darker);
            color: white;
            position: sticky;
            top: 0;
        }
        .pokemon-table tr:hover {
            background-color: #f5f5f5;
        }
        .pokemon-img {
            width: 50px;
            height: 50px;
        }
        .type {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            color: white;
            font-size: 12px;
            margin-right: 4px;
        }
        .type-normal { background-color: #A8A878; }
        .type-feu { background-color: #F08030; }
        .type-eau { background-color: #6890F0; }
        .type-plante { background-color: #78C850; }
        .type-electrique { background-color: #F8D030; }
        .type-glace { background-color: #98D8D8; }
        .type-combat { background-color: #C03028; }
        .type-poison { background-color: #A040A0; }
        .type-sol { background-color: #E0C068; }
        .type-vol { background-color: #A890F0; }
        .type-psy { background-color: #F85888; }
        .type-insecte { background-color: #A8B820; }
        .type-roche { background-color: #B8A038; }
        .type-spectre { background-color: #705898; }
        .type-dragon { background-color: #7038F8; }
        .stats-bar-container {
            width: 100px;
            background-color: #ddd;
            border-radius: 4px;
        }
        .stats-bar {
            height: 10px;
            border-radius: 4px;
            background-color: var(--red-lighter);
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
            <ul>
                <li><a href="index.php">Accueil</a></li>
            </ul>
        </div>
        
        <main>
            <div id="search-bar">
                <div class="input-group">
                    <label for="searchInput">Rechercher :</label>
                    <input type="text" id="searchInput" placeholder="Nom du Pokémon...">
                </div>
            </div>
            
            <article>
                <table class="pokemon-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Image</th>
                            <th>Nom</th>
                            <th>Types</th>
                            <th>PV</th>
                            <th>Attaque</th>
                            <th>Défense</th>
                            <th>Vitesse</th>
                            <th>Spécial</th>
                            <th>Date d'ajout</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($pokemon = mysqli_fetch_assoc($result)) : ?>
                            <tr>
                                <td><?php echo $pokemon['idPokemon']; ?></td>
                                <td>
                                    <?php if (!empty($pokemon['urlPhoto'])) : ?>
                                        <img src="<?php echo htmlspecialchars($pokemon['urlPhoto']); ?>" alt="<?php echo htmlspecialchars($pokemon['nomPokemon']); ?>" class="pokemon-img">
                                    <?php else : ?>
                                        <span>Pas d'image</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($pokemon['nomPokemon']); ?></td>
                                <td>
                                    <span class="type type-<?php echo strtolower($pokemon['type1']); ?>"><?php echo htmlspecialchars($pokemon['type1']); ?></span>
                                    <?php if (!empty($pokemon['type2'])) : ?>
                                        <span class="type type-<?php echo strtolower($pokemon['type2']); ?>"><?php echo htmlspecialchars($pokemon['type2']); ?></span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="stats-bar-container">
                                        <div class="stats-bar" style="width: <?php echo min(100, $pokemon['PV'] / 2.5); ?>%;"></div>
                                    </div>
                                    <?php echo $pokemon['PV']; ?>
                                </td>
                                <td>
                                    <div class="stats-bar-container">
                                        <div class="stats-bar" style="width: <?php echo min(100, $pokemon['PtsAttaque'] / 1.5); ?>%;"></div>
                                    </div>
                                    <?php echo $pokemon['PtsAttaque']; ?>
                                </td>
                                <td>
                                    <div class="stats-bar-container">
                                        <div class="stats-bar" style="width: <?php echo min(100, $pokemon['PtsDefense'] / 1.5); ?>%;"></div>
                                    </div>
                                    <?php echo $pokemon['PtsDefense']; ?>
                                </td>
                                <td>
                                    <div class="stats-bar-container">
                                        <div class="stats-bar" style="width: <?php echo min(100, $pokemon['PtsVitesse'] / 1.5); ?>%;"></div>
                                    </div>
                                    <?php echo $pokemon['PtsVitesse']; ?>
                                </td>
                                <td>
                                    <div class="stats-bar-container">
                                        <div class="stats-bar" style="width: <?php echo min(100, $pokemon['PtsSpecial'] / 1.5); ?>%;"></div>
                                    </div>
                                    <?php echo $pokemon['PtsSpecial']; ?>
                                </td>
                                <td><?php echo date('d/m/Y', strtotime($pokemon['DateAjout'])); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </article>
        </main>
    </div>
    
    <footer>
        <p>© 2025 Pokédex - Tous droits réservés</p>
    </footer>

    <script>
        // Script pour la recherche de Pokémon
        document.getElementById('searchInput').addEventListener('keyup', function() {
            const input = this.value.toLowerCase();
            const rows = document.querySelectorAll('.pokemon-table tbody tr');
            
            rows.forEach(row => {
                const name = row.querySelector('td:nth-child(3)').textContent.toLowerCase();
                if (name.includes(input)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
    </script>
</body>
</html>

<?php
// Fermeture de la connexion
mysqli_close($databaseConnection);
?>