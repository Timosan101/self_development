document.addEventListener("DOMContentLoaded", () => {
    let lastScrollY = window.scrollY;
    const header = document.querySelector('.header');

    if (!header) {
        console.error("Header element with class .header was not found!");
        return;
    }

    window.addEventListener('scroll', () => {
        const currentScrollY = window.scrollY;

        // WHEN SCROLLING DOWN PAST 100PX, HIDE THE HEADER
        if (currentScrollY > lastScrollY && currentScrollY > 100) {
            header.classList.add('nav-hidden');
        } 
        // WHEN SCROLLING UP, RE-SHOW THE HEADER
        else if (currentScrollY < lastScrollY) {
            header.classList.remove('nav-hidden');
        }

        lastScrollY = currentScrollY;
    });
});