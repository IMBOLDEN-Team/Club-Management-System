<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>KPMIM Footer Only</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-black text-white font-sans flex items-center justify-center px-5">

  <!-- Fullscreen footer-style content -->
  <div class="w-full max-w-6xl space-y-10">

    <!-- Top Footer Content -->
    <div class="flex flex-wrap justify-between gap-8">
      
      <!-- Column 1 -->
      <div class="min-w-[200px] flex-1">
        <h3 class="mb-3 text-lg font-semibold">About</h3>
        <ul class="space-y-2">
          <li><a href="#" class="hover:underline">About Us</a></li>
          <li><a href="#" class="hover:underline">Vision & Mission</a></li>
        </ul>
      </div>

      <!-- Column 2 -->
      <div class="min-w-[200px] flex-1">
        <h3 class="mb-3 text-lg font-semibold">Support</h3>
        <ul class="space-y-2">
          <li><a href="#" class="hover:underline">Contact Us</a></li>
          <li><a href="#" class="hover:underline">Help Center</a></li>
          <li><a href="#" class="hover:underline">Privacy Policy</a></li>
        </ul>
      </div>

      <!-- Column 3 -->
      <div class="min-w-[200px] flex-1">
        <h3 class="mb-3 text-lg font-semibold">Connect with Us</h3>
        <ul class="space-y-2">
          <li>
            <a href="https://www.facebook.com/kpmim.official" target="_blank" class="inline-flex items-center gap-2 hover:opacity-80">
              <img src="img/facebook.png" alt="Facebook" class="w-6 h-6 object-contain" />
              <span>Facebook</span>
            </a>
          </li>
          <li>
            <a href="https://t.me/kpmimchannel" target="_blank" class="inline-flex items-center gap-2 hover:opacity-80">
              <img src="img/telegram.png" alt="Telegram" class="w-6 h-6 object-contain" />
              <span>Telegram Channel</span>
            </a>
          </li>
        </ul>
      </div>
    </div>

    <!-- Google Maps Placeholder -->
    <div>
      <h3 class="text-lg font-semibold mb-3">Our Location</h3>
      <div class="w-full h-52 bg-gray-800 text-gray-300 text-sm rounded flex items-center justify-center">
        Google Maps location of KPMIM will appear here.
      </div>
    </div>

    <!-- Copyright -->
    <div class="border-t border-gray-700 pt-5 text-center text-sm text-gray-400">
      © <?php echo date("Y"); ?> Kolej Profesional MARA Indera Mahkota (KPMIM). All rights reserved.
    </div>
  </div>

</body>
</html>
