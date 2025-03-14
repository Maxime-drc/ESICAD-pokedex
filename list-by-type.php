<?php
require_once("head.php");
$pdo = new PDO("mysql:host=localhost;dbname=pokemon", "root", "", [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"
]);
$types = $pdo->query("SELECT * FROM type_pokemon ORDER BY nomType")->fetchAll(PDO::FETCH_ASSOC);

$resultats = [];
if (!empty($_GET['type'])) {
    $idType = (int)$_GET['type'];
    $stmt = $pdo->prepare("SELECT p.*, t1.nomType AS typePrimaire, t2.nomType AS typeSecondaire 
                            FROM pokemon p
                            INNER JOIN type_pokemon t1 ON p.idType1 = t1.idType
                            LEFT JOIN type_pokemon t2 ON p.idType2 = t2.idType
                            WHERE p.idType1 = :idType OR p.idType2 = :idType");
    $stmt->execute(['idType' => $idType]);
    $resultats = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Recherche de Pokémon</title>
</head>
<body>
    <form method="GET">
        <select name="type">
            <option value="">Sélectionnez un type</option>
            <?php foreach ($types as $type): ?>
                <option value="<?= $type['idType'] ?>" <?= ($_GET['type'] ?? '') == $type['idType'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($type['nomType']) ?>
                </option>
            <?php endforeach; ?>
        </select>
        <button type="submit">Rechercher</button>
    </form>
    <div>
        <?php if (!empty($_GET['type'])): ?>
            <?php if ($resultats): ?>
                <?php foreach ($resultats as $pokemon): ?>
                    <div>
                        <h3>#<?= str_pad($pokemon['idPokemon'], 3, '0', STR_PAD_LEFT) ?> - <?= htmlspecialchars($pokemon['nomPokemon']) ?></h3>
                        <?php if (!empty($pokemon['urlPhoto'])): ?>
                            <img src="<?= htmlspecialchars($pokemon['urlPhoto']) ?>" alt="<?= htmlspecialchars($pokemon['nomPokemon']) ?>">
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>Aucun Pokémon trouvé.</p>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</body>
</html>
