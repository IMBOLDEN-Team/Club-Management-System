<?php
/**
 * Telegram Notification Component
 * Handles sending notifications to Telegram via Bot API
 */

class TelegramNotification {
    private $botToken;
    private $chatId;
    private $apiUrl;
    
    public function __construct($botToken = null, $chatId = null) {
        // Default values from the requirements
        $this->botToken = $botToken ?? '7869816284:AAEhc7Lyqflzi9Pn4_C7ItnhnKYWvTKnlMk';
        $this->chatId = $chatId ?? '-1002626399560';
        $this->apiUrl = "https://api.telegram.org/bot{$this->botToken}/";
    }
    
    /**
     * Send a message to Telegram
     * @param string $message The message to send
     * @return array Response from Telegram API
     */
    public function sendMessage($message) {
        $data = [
            'chat_id' => $this->chatId,
            'text' => $message,
            'parse_mode' => 'HTML'
        ];
        
        return $this->makeRequest('sendMessage', $data);
    }
    
    /**
     * Send a photo to Telegram with caption
     * @param string $photoData Base64 encoded photo data
     * @param string $caption The caption for the photo
     * @return array Response from Telegram API
     */
    public function sendPhoto($photoData, $caption = '') {
        // Create a temporary file for the photo
        $tempFile = tempnam(sys_get_temp_dir(), 'telegram_photo_');
        file_put_contents($tempFile, base64_decode($photoData));
        
        $data = [
            'chat_id' => $this->chatId,
            'photo' => new CURLFile($tempFile),
            'caption' => $caption,
            'parse_mode' => 'HTML'
        ];
        
        $response = $this->makeRequest('sendPhoto', $data);
        
        // Clean up temporary file
        unlink($tempFile);
        
        return $response;
    }
    
    /**
     * Send activity notification
     * @param string $activityName Activity name
     * @param string $clubName Club name
     * @param string $start Start date/time
     * @param string $end End date/time
     * @param int $meritPoint Merit points
     * @param string $clubLogo Base64 encoded logo (optional)
     * @return array Response from Telegram API
     */
    public function sendActivityNotification($activityName, $clubName, $start, $end, $meritPoint, $clubLogo = null) {
        $message = $this->formatActivityMessage($activityName, $clubName, $start, $end, $meritPoint);
        
        // If club logo is provided, send as photo with caption
        if ($clubLogo) {
            return $this->sendPhoto($clubLogo, $message);
        }
        
        // Otherwise send as text message
        return $this->sendMessage($message);
    }
    
    /**
     * Format activity message for Telegram
     * @param string $activityName Activity name
     * @param string $clubName Club name
     * @param string $start Start date/time
     * @param string $end End date/time
     * @param int $meritPoint Merit points
     * @return string Formatted message
     */
    public function formatActivityMessage($activityName, $clubName, $start, $end, $meritPoint) {
        $startFormatted = date('M d, Y g:i A', strtotime($start));
        $endFormatted = date('M d, Y g:i A', strtotime($end));
        
        $message = "🎉 <b>New Activity Created!</b>\n\n";
        $message .= "📋 <b>Activity:</b> {$activityName}\n";
        $message .= "🏢 <b>Club:</b> {$clubName}\n";
        $message .= "⏰ <b>Start:</b> {$startFormatted}\n";
        $message .= "⏰ <b>End:</b> {$endFormatted}\n";
        $message .= "⭐ <b>Merit Points:</b> {$meritPoint}\n\n";
        $message .= "Join now and earn merit points! 🚀";
        
        return $message;
    }

    /**
     * Send a photo to Telegram from file path
     * @param string $filePath Absolute path to the image file
     * @param string $caption Optional caption
     * @return array Response from Telegram API
     */
    public function sendPhotoFromPath($filePath, $caption = '') {
        if (!is_readable($filePath)) {
            return [
                'ok' => false,
                'description' => 'Photo file not readable'
            ];
        }

        $data = [
            'chat_id' => $this->chatId,
            'photo' => new CURLFile($filePath),
            'caption' => $caption,
            'parse_mode' => 'HTML'
        ];

        return $this->makeRequest('sendPhoto', $data);
    }

    /**
     * Send a document (e.g., PDF) to Telegram from file path
     * @param string $filePath Absolute path to the document
     * @param string $caption Optional caption
     * @return array Response from Telegram API
     */
    public function sendDocumentFromPath($filePath, $caption = '') {
        if (!is_readable($filePath)) {
            return [
                'ok' => false,
                'description' => 'Document file not readable'
            ];
        }

        $data = [
            'chat_id' => $this->chatId,
            'document' => new CURLFile($filePath),
            'caption' => $caption,
            'parse_mode' => 'HTML'
        ];

        return $this->makeRequest('sendDocument', $data);
    }

    /**
     * Send activity notification with an attachment (photo or PDF)
     * @param string $activityName
     * @param string $clubName
     * @param string $start
     * @param string $end
     * @param int $meritPoint
     * @param string $attachmentPath Absolute path
     * @param string $mimeType Detected mime type
     * @return array Response from Telegram API
     */
    public function sendActivityWithAttachment($activityName, $clubName, $start, $end, $meritPoint, $attachmentPath, $mimeType) {
        $caption = $this->formatActivityMessage($activityName, $clubName, $start, $end, $meritPoint);

        if (strpos($mimeType, 'image/') === 0) {
            return $this->sendPhotoFromPath($attachmentPath, $caption);
        }

        // Default to document for non-image (e.g., PDF)
        return $this->sendDocumentFromPath($attachmentPath, $caption);
    }
    
    /**
     * Make HTTP request to Telegram API
     * @param string $method API method
     * @param array $data Request data
     * @return array Response data
     */
    private function makeRequest($method, $data = []) {
        $url = $this->apiUrl . $method;
        
        // Check if we're uploading a file
        $hasFile = false;
        foreach ($data as $value) {
            if ($value instanceof CURLFile) {
                $hasFile = true;
                break;
            }
        }
        
        if ($hasFile) {
            // Use cURL for file uploads
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            
            $result = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            if ($result === false || $httpCode !== 200) {
                return [
                    'ok' => false,
                    'error_code' => 0,
                    'description' => 'Failed to connect to Telegram API'
                ];
            }
        } else {
            // Use file_get_contents for regular requests
            $options = [
                'http' => [
                    'header' => "Content-type: application/x-www-form-urlencoded\r\n",
                    'method' => 'POST',
                    'content' => http_build_query($data)
                ]
            ];
            
            $context = stream_context_create($options);
            $result = file_get_contents($url, false, $context);
            
            if ($result === false) {
                return [
                    'ok' => false,
                    'error_code' => 0,
                    'description' => 'Failed to connect to Telegram API'
                ];
            }
        }
        
        return json_decode($result, true) ?? [
            'ok' => false,
            'error_code' => 0,
            'description' => 'Invalid response from Telegram API'
        ];
    }
    
    /**
     * Test the Telegram connection
     * @return array Test result
     */
    public function testConnection() {
        $response = $this->sendMessage("🤖 <b>Test Message</b>\n\nThis is a test message from the Club Management System.\n\nTime: " . date('Y-m-d H:i:s'));
        
        if ($response['ok'] ?? false) {
            return [
                'success' => true,
                'message' => 'Telegram connection successful!',
                'response' => $response
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Telegram connection failed: ' . ($response['description'] ?? 'Unknown error'),
                'response' => $response
            ];
        }
    }
}
?>
