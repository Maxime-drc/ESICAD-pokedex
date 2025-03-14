<?php
$databaseConnection = mysqli_connect('localhost', 'root', '', 'pokemon', '3306')
    or die('Erreur de connexion à la base de données');
mysqli_set_charset($databaseConnection, 'utf8mb4');

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
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <header><a href="index.php"><h1>Pokédex</h1></a></header>
    <main>
        <input type="text" id="searchInput" placeholder="Rechercher un Pokémon...">
        <table>
            <thead>
                <tr>
                    <th>#</th><th>Image</th><th>Nom</th><th>Types</th>
                    <th>PV</th><th>Attaque</th><th>Défense</th>
                    <th>Vitesse</th><th>Spécial</th><th>Date</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($pokemon = mysqli_fetch_assoc($result)) : ?>
                    <tr>
                        <td><?= $pokemon['idPokemon'] ?></td>
                        <td>
                            <?= !empty($pokemon['urlPhoto']) 
                                ? "<img src='".htmlspecialchars($pokemon['urlPhoto'])."' alt='".htmlspecialchars($pokemon['nomPokemon'])."'>" 
                                : "Pas d'image" ?>
                        </td>
                        <td><?= htmlspecialchars($pokemon['nomPokemon']) ?></td>
                        <td>
                            <span class="type type-<?= strtolower($pokemon['type1']) ?>">
                                <?= htmlspecialchars($pokemon['type1']) ?>
                            </span>
                            <?php if ($pokemon['type2']) : ?>
                                <span class="type type-<?= strtolower($pokemon['type2']) ?>">
                                    <?= htmlspecialchars($pokemon['type2']) ?>
                                </span>
                            <?php endif; ?>
                        </td>
                        <?php foreach (["PV", "PtsAttaque", "PtsDefense", "PtsVitesse", "PtsSpecial"] as $stat) : ?>
                            <td><div class="stats-bar" style="width: <?= min(100, $pokemon[$stat] / 1.5) ?>%"></div>
                                <?= $pokemon[$stat] ?></td>
                        <?php endforeach; ?>
                        <td><?= date('d/m/Y', strtotime($pokemon['DateAjout'])) ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </main>
    <script>
        document.getElementById('searchInput').addEventListener('keyup', function() {
            const input = this.value.toLowerCase();
            document.querySelectorAll('tbody tr').forEach(row => {
                row.style.display = row.children[2].textContent.toLowerCase().includes(input) ? '' : 'none';
            });
        });
    </script>
</body>
</html>
<?php mysqli_close($databaseConnection); ?>
