<?php
$page_title = 'Mes Pokémon';
require_once('config.php');
require_once('header.php');

// Vérifier si l'utilisateur est connecté
requireLogin();

$user_id = $_SESSION['user_id'];

// Récupérer la liste des Pokémon capturés par l'utilisateur
$sql = "SELECT cp.id as capture_id, cp.date_capture, p.id as pokemon_id, p.nom, p.numero 
        FROM capture_pokemon cp
        JOIN pokemon p ON cp.pokemon_id = p.id
        WHERE cp.user_id = ?
        ORDER BY cp.date_capture DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$captures = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $captures[] = $row;
    }
}
?>

<h1>Mes Pokémon capturés</h1>

<?php if (empty($captures)): ?>
    <div class="info-message">
        Vous n'avez pas encore capturé de Pokémon. 
        <a href="capture.php">Capturez votre premier Pokémon maintenant !</a>
    </div>
<?php else: ?>
    <div class="pokemon-list">
        <table>
            <thead>
                <tr>
                    <th>N°</th>
                    <th>Pokémon</th>
                    <th>Date de capture</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($captures as $capture): ?>
                    <tr>
                        <td>#<?php echo $capture['numero']; ?></td>
                        <td><?php echo htmlspecialchars($capture['nom']); ?></td>
                        <td><?php echo date('d/m/Y', strtotime($capture['date_capture'])); ?></td>
                        <td>
                            <a href="pokemon_details.php?id=<?php echo $capture['pokemon_id']; ?>" class="btn-small">Détails</a>
                            <form method="post" action="supprimer_capture.php" style="display: inline;">
                                <input type="hidden" name="capture_id" value="<?php echo $capture['capture_id']; ?>">
                                <button type="submit" class="btn-small btn-danger" onclick="return confirm('Êtes-vous sûr de vouloir libérer ce Pokémon ?')">Libérer</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    
    <div class="stats-section">
        <h2>Statistiques</h2>
        <p>Nombre total de Pokémon capturés: <?php echo count($captures); ?></p>
        <?php
        // Compter le nombre de Pokémon uniques
        $pokemon_uniques = [];
        foreach ($captures as $capture) {
            $pokemon_uniques[$capture['pokemon_id']] = true;
        }
        ?>
        <p>Nombre de Pokémon uniques: <?php echo count($pokemon_uniques); ?></p>
    </div>
<?php endif; ?>

<?php require_once('footer.php'); ?>

<?php
session_start();
require_once('config.php');

// Vérifier si l'utilisateur est connecté
requireLogin();

$user_id = $_SESSION['user_id'];
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['capture_id'])) {
    $capture_id = (int)$_POST['capture_id'];
    
    // Vérifier que la capture appartient bien à l'utilisateur
    $sql = "SELECT id FROM capture_pokemon WHERE id = ? AND user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $capture_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        // Supprimer la capture
        $sql = "DELETE FROM capture_pokemon WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $capture_id);
        
        if ($stmt->execute()) {
            // Redirection vers la liste des Pokémon avec message de succès
            $_SESSION['message'] = "Le Pokémon a été libéré avec succès.";
            header('Location: mes_pokemons.php');
            exit;
        } else {
            $error = "Erreur lors de la libération du Pokémon: " . $conn->error;
        }
    } else {
        $error = "Vous n'êtes pas autorisé à libérer ce Pokémon.";
    }
} else {
    $error = "Requête invalide.";
}

// En cas d'erreur
$_SESSION['error'] = $error;
header('Location: mes_pokemons.php');
exit;