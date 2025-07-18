<!-- footer.php -->
<footer class="bg-black text-white py-10 px-4 sm:px-6 lg:px-8 font-sans mt-auto">
  <div class="max-w-7xl mx-auto">
    <!-- Main Footer Columns - Modified for mobile layout -->
    <div class="flex flex-col md:grid md:grid-cols-3 gap-8 mb-10">
      <!-- First Row for Mobile -->
      <div class="flex flex-row justify-between md:contents">
        <!-- Column 1: About -->
        <div class="w-[48%] md:w-auto">
          <h3 class="text-lg font-semibold mb-4">About</h3>
          <ul class="space-y-3">
            <li><a href="#about" class="hover:underline text-gray-300">About Us</a></li>
          </ul>
        </div>

        <!-- Column 2: Support - Right side on mobile -->
        <div class="w-[48%] md:w-auto">
          <h3 class="text-lg font-semibold mb-4">Support</h3>
          <ul class="space-y-3">
            <li><a href="#" class="hover:underline text-gray-300">Contact Us</a></li>
            <li><a href="#" class="hover:underline text-gray-300">Help Center</a></li>
            <li><a href="#" class="hover:underline text-gray-300">Privacy Policy</a></li>
          </ul>
        </div>
      </div>

      <!-- Column 3: Social Media - Full width below on mobile -->
      <div class="md:mt-0">
        <h3 class="text-lg font-semibold mb-4">Connect with Us</h3>
        <ul class="space-y-3">
          <li>
            <a href="https://www.facebook.com/kpmim.official" target="_blank" class="inline-flex items-center gap-3 hover:opacity-80 text-gray-300">
              <img src="img/facebook.png" alt="Facebook" class="w-5 h-5"> Facebook
            </a>
          </li>
          <li>
            <a href="https://t.me/kpmimchannel" target="_blank" class="inline-flex items-center gap-3 hover:opacity-80 text-gray-300">
              <img src="img/telegram.png" alt="Telegram" class="w-5 h-5"> Telegram Channel
            </a>
          </li>
        </ul>
      </div>
    </div>

    <!-- Location Section - Unchanged position -->
    <div class="w-full mt-12">
      <h3 class="text-lg font-semibold mb-4">Our Location</h3>
      <p class="text-gray-300 mb-5">Kolej Profesional MARA Indera Mahkota, Indera Mahkota 15, 25200 Kuantan, Pahang</p>
      <div id="map-container" class="w-full h-64 md:h-80 lg:h-96 bg-gray-800 rounded-lg overflow-hidden relative">
        <div class="absolute inset-0 flex items-center justify-center text-gray-300">
          Loading map...
        </div>
      </div>
      <p class="text-xs text-gray-500 mt-3 text-center">
        Powered by Geoapify | © OpenStreetMap contributors
      </p>
    </div>

    <!-- Copyright -->
    <div class="border-t border-gray-700 mt-10 pt-6 text-center text-sm text-gray-400">
      © <?php echo date("Y"); ?> Kolej Profesional MARA Indera Mahkota (KPMIM). All rights reserved.
    </div>
  </div>
</footer>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const apiKey = 'abf751b8c5ad4e109699af2d71846148';
    const address = 'Kolej Profesional MARA Indera Mahkota, 25200 Kuantan, Pahang';
    
    fetch(`https://api.geoapify.com/v1/geocode/search?text=${encodeURIComponent(address)}&apiKey=${apiKey}`)
        .then(response => response.json())
        .then(data => {
            if (data.features && data.features.length > 0) {
                const [lon, lat] = data.features[0].geometry.coordinates;
                // Get container dimensions to perfectly fill the space
                const container = document.getElementById('map-container');
                const width = container.offsetWidth;
                const height = container.offsetHeight;
                
                const mapUrl = `https://maps.geoapify.com/v1/staticmap?style=osm-bright&width=${width}&height=${height}&center=lonlat:${lon},${lat}&zoom=16.5&marker=lonlat:${lon},${lat};color:red;size:large&apiKey=${apiKey}`;
                
                const mapImg = document.createElement('img');
                mapImg.src = mapUrl;
                mapImg.className = 'w-full h-full object-cover absolute inset-0';
                mapImg.alt = 'KPMIM Location Map';
                
                container.querySelector('div').remove();
                container.appendChild(mapImg);
            }
        })
        .catch(error => {
            console.error('Error fetching map:', error);
            document.getElementById('map-container').innerHTML = '<div class="w-full h-full flex items-center justify-center text-white">Map loading failed. Please try again later.</div>';
        });
});
</script>