<?php
// Activer l'affichage des erreurs pour debug
error_reporting(E_ALL);
ini_set('display_errors', 0); // Ne pas afficher dans le navigateur

// Headers
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

// Fonction de log
function logMessage($message) {
    $logFile = 'contact_debug.log';
    $timestamp = date('Y-m-d H:i:s');
    file_put_contents($logFile, "[$timestamp] $message\n", FILE_APPEND);
}

logMessage("=== Nouvelle requête ===");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    try {
        // Récupération des données
        $name = isset($_POST['name']) ? trim($_POST['name']) : '';
        $email = isset($_POST['email']) ? trim($_POST['email']) : '';
        $phone = isset($_POST['phone']) ? trim($_POST['phone']) : 'Non renseigné';
        $subject = isset($_POST['subject']) ? trim($_POST['subject']) : '';
        $message = isset($_POST['message']) ? trim($_POST['message']) : '';
        
        logMessage("Données reçues: name=$name, email=$email");
        
        // Validation
        if (empty($name) || empty($email) || empty($subject) || empty($message)) {
            logMessage("Validation échouée: champs manquants");
            echo json_encode([
                'success' => false,
                'message' => 'Tous les champs obligatoires doivent être remplis.'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            logMessage("Validation échouée: email invalide");
            echo json_encode([
                'success' => false,
                'message' => 'L\'adresse email est invalide.'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }
        
        // Nettoyage des données
        $name = htmlspecialchars($name);
        $email = htmlspecialchars($email);
        $phone = htmlspecialchars($phone);
        $subject = htmlspecialchars($subject);
        $message = htmlspecialchars($message);
        
        // 1. Sauvegarder dans un fichier (TOUJOURS)
        $backupFile = 'demandes_contact.txt';
        $backupContent = "\n\n==========================================\n";
        $backupContent .= "Date: " . date('d/m/Y à H:i:s') . "\n";
        $backupContent .= "==========================================\n";
        $backupContent .= "Nom/Entreprise: $name\n";
        $backupContent .= "Email: $email\n";
        $backupContent .= "Téléphone: $phone\n";
        $backupContent .= "Service demandé: $subject\n";
        $backupContent .= "Message:\n$message\n";
        $backupContent .= "==========================================\n";
        
        $saved = file_put_contents($backupFile, $backupContent, FILE_APPEND);
        logMessage("Sauvegarde fichier: " . ($saved ? "OK" : "ÉCHEC"));
        
        if (!$saved) {
            logMessage("ERREUR: Impossible de sauvegarder le fichier");
            echo json_encode([
                'success' => false,
                'message' => 'Erreur d\'enregistrement. Contactez-nous au +41 78 338 71 63.'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }
        
        // 2. Tenter d'envoyer un email
        $to = 'shineyoursservice@gmail.com';
        $emailSubject = "Nouvelle demande de devis - $subject";
        
        $emailBody = "NOUVELLE DEMANDE DE DEVIS\n\n";
        $emailBody .= "Nom/Entreprise: $name\n";
        $emailBody .= "Email: $email\n";
        $emailBody .= "Téléphone: $phone\n";
        $emailBody .= "Service: $subject\n\n";
        $emailBody .= "Message:\n$message\n\n";
        $emailBody .= "---\n";
        $emailBody .= "Date: " . date('d/m/Y à H:i') . "\n";
        
        // Headers email
        $headers = "From: contact@shineyours.ch\r\n";
        $headers .= "Reply-To: $email\r\n";
        $headers .= "X-Mailer: PHP/" . phpversion() . "\r\n";
        $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
        
        // Tentative d'envoi
        $mailSent = @mail($to, $emailSubject, $emailBody, $headers);
        logMessage("Envoi email: " . ($mailSent ? "OK" : "ÉCHEC"));
        
        // Réponse succès (même si l'email n'est pas parti, le fichier est sauvegardé)
        echo json_encode([
            'success' => true,
            'message' => 'Merci ! Votre demande a été enregistrée avec succès. Nous vous contacterons rapidement.' . 
                        ($mailSent ? '' : ' (Une notification a été sauvegardée)')
        ], JSON_UNESCAPED_UNICODE);
        
        logMessage("Succès complet");
        
    } catch (Exception $e) {
        logMessage("EXCEPTION: " . $e->getMessage());
        echo json_encode([
            'success' => false,
            'message' => 'Erreur serveur. Contactez-nous au +41 78 338 71 63.'
        ], JSON_UNESCAPED_UNICODE);
    }
    
} else {
    logMessage("Méthode non POST");
    echo json_encode([
        'success' => false,
        'message' => 'Méthode non autorisée'
    ], JSON_UNESCAPED_UNICODE);
}
?>