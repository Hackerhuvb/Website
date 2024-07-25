let currentIndex = 0;
        const items = document.querySelectorAll('.carousel-item');
        
        function showNextSlide() {
            items[currentIndex].style.display = 'none';
            currentIndex = (currentIndex + 1) % items.length;
            items[currentIndex].style.display = 'flex';
        }
        
        // Initialize carousel
        items.forEach((item, index) => {
            item.style.display = index === 0 ? 'flex' : 'none';
        });
        
        setInterval(showNextSlide, 3000); // Change slide every 3 seconds
        
        // Function to toggle menu on small screens
        function toggleMenu() {
            const menu = document.getElementById('menu');
            menu.classList.toggle('open');
        }