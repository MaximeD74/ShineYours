<?php
header('Content-Type: application/json');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    $name = htmlspecialchars(trim($_POST['name'] ?? ''));
    $email = filter_var(trim($_POST['email'] ?? ''), FILTER_SANITIZE_EMAIL);
    $phone = htmlspecialchars(trim($_POST['phone'] ?? 'Non renseigné'));
    $subject = htmlspecialchars(trim($_POST['subject'] ?? ''));
    $message = htmlspecialchars(trim($_POST['message'] ?? ''));
    
    // Validation
    if (empty($name) || empty($email) || empty($subject) || empty($message)) {
        echo json_encode([
            'success' => false,
            'message' => 'Tous les champs obligatoires doivent être remplis.'
        ]);
        exit;
    }
    
    // Sauvegarder dans un fichier
    $filename = 'demandes_contact.txt';
    $content = "\n\n========================================\n";
    $content .= "Date: " . date('d/m/Y H:i:s') . "\n";
    $content .= "========================================\n";
    $content .= "Nom/Entreprise: $name\n";
    $content .= "Email: $email\n";
    $content .= "Téléphone: $phone\n";
    $content .= "Service demandé: $subject\n";
    $content .= "Message:\n$message\n";
    $content .= "========================================\n";
    
    if (file_put_contents($filename, $content, FILE_APPEND)) {
        
        // Envoyer aussi un email de notification simple
        $to = 'shineyoursservice@gmail.com';
        $subject_email = "Nouvelle demande de devis";
        $headers = "From: noreply@shineyours.ch\r\n";
        
        @mail($to, $subject_email, $content, $headers);
        
        echo json_encode([
            'success' => true,
            'message' => 'Merci ! Votre demande a été enregistrée. Nous vous contacterons rapidement.'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Erreur d\'enregistrement. Veuillez nous contacter au +41 78 338 71 63.'
        ]);
    }
    
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Méthode non autorisée'
    ]);
}
?>