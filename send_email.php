<?php
// Activer l'affichage des erreurs pour debug
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Configuration
$to_email = 'shineyoursservice@gmail.com';

// Headers JSON
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');

// Log pour debug
$log_file = 'contact_log.txt';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    try {
        // Récupération des données
        $name = isset($_POST['name']) ? htmlspecialchars(trim($_POST['name'])) : '';
        $email = isset($_POST['email']) ? filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL) : '';
        $phone = isset($_POST['phone']) ? htmlspecialchars(trim($_POST['phone'])) : 'Non renseigné';
        $subject = isset($_POST['subject']) ? htmlspecialchars(trim($_POST['subject'])) : '';
        $message = isset($_POST['message']) ? htmlspecialchars(trim($_POST['message'])) : '';
        
        // Log les données reçues
        file_put_contents($log_file, date('Y-m-d H:i:s') . " - Données reçues\n", FILE_APPEND);
        
        // Validation
        $errors = [];
        
        if (empty($name)) {
            $errors[] = "Le nom est obligatoire";
        }
        
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "L'email est invalide";
        }
        
        if (empty($subject)) {
            $errors[] = "L'objet est obligatoire";
        }
        
        if (empty($message)) {
            $errors[] = "Le message est obligatoire";
        }
        
        // Si erreurs
        if (!empty($errors)) {
            echo json_encode([
                'success' => false,
                'message' => implode(', ', $errors)
            ]);
            exit;
        }
        
        // Préparation de l'email
        $email_subject = "Nouvelle demande - " . $subject;
        
        $email_body = "NOUVELLE DEMANDE DE DEVIS\n\n";
        $email_body .= "Nom/Entreprise: " . $name . "\n";
        $email_body .= "Email: " . $email . "\n";
        $email_body .= "Téléphone: " . $phone . "\n";
        $email_body .= "Service: " . $subject . "\n\n";
        $email_body .= "Message:\n" . $message . "\n\n";
        $email_body .= "Date: " . date('d/m/Y H:i') . "\n";
        
        // Headers
        $headers = "From: noreply@shineyours.ch\r\n";
        $headers .= "Reply-To: " . $email . "\r\n";
        $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
        
        // Tentative d'envoi
        $mail_sent = mail($to_email, $email_subject, $email_body, $headers);
        
        // Log le résultat
        file_put_contents($log_file, date('Y-m-d H:i:s') . " - Mail sent: " . ($mail_sent ? 'YES' : 'NO') . "\n", FILE_APPEND);
        
        if ($mail_sent) {
            echo json_encode([
                'success' => true,
                'message' => 'Merci ! Votre demande a été envoyée avec succès.'
            ]);
        } else {
            // Enregistrer dans un fichier en cas d'échec
            $backup_file = 'contacts_backup.txt';
            $backup_content = "\n\n--- " . date('Y-m-d H:i:s') . " ---\n";
            $backup_content .= $email_body;
            file_put_contents($backup_file, $backup_content, FILE_APPEND);
            
            echo json_encode([
                'success' => false,
                'message' => 'Le serveur mail n\'est pas configuré. Votre demande a été sauvegardée. Contactez-nous au +41 78 338 71 63.'
            ]);
        }
        
    } catch (Exception $e) {
        file_put_contents($log_file, date('Y-m-d H:i:s') . " - Error: " . $e->getMessage() . "\n", FILE_APPEND);
        
        echo json_encode([
            'success' => false,
            'message' => 'Erreur serveur. Contactez-nous directement au +41 78 338 71 63.'
        ]);
    }
    
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Méthode non autorisée'
    ]);
}
?>