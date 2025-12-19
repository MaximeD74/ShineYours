<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php'; // Si vous utilisez Composer

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
    
    $mail = new PHPMailer(true);
    
    try {
        // Configuration SMTP Gmail
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'shineyoursservice@gmail.com'; // Votre email Gmail
        $mail->Password = 'votre_mot_de_passe_app'; // Mot de passe d'application Gmail
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;
        $mail->CharSet = 'UTF-8';
        
        // Destinataires
        $mail->setFrom('shineyoursservice@gmail.com', 'Site Shine Yours');
        $mail->addAddress('shineyoursservice@gmail.com');
        $mail->addReplyTo($email, $name);
        
        // Contenu
        $mail->isHTML(false);
        $mail->Subject = "Nouvelle demande - $subject";
        $mail->Body = "NOUVELLE DEMANDE DE DEVIS\n\n" .
                      "Nom/Entreprise: $name\n" .
                      "Email: $email\n" .
                      "Téléphone: $phone\n" .
                      "Service: $subject\n\n" .
                      "Message:\n$message\n\n" .
                      "Date: " . date('d/m/Y H:i');
        
        $mail->send();
        
        echo json_encode([
            'success' => true,
            'message' => 'Merci ! Votre demande a été envoyée avec succès.'
        ]);
        
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Erreur d\'envoi: ' . $mail->ErrorInfo
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Méthode non autorisée'
    ]);
}
?>