// Initialize AOS
AOS.init({ duration:1200, once:true });

// Tilt effect on mission cards
VanillaTilt.init(document.querySelectorAll("[data-tilt]"), {
  max: 15,
  speed: 400,
  glare: true,
  "max-glare":0.3
});

// Hero typing effect
new Typed(".typing", {
  strings: ["Efficiency.", "Innovation.", "Growth."],
  typeSpeed: 80,
  backSpeed: 50,
  loop: true
});

// Scroll arrow function
function scrollToNext() {
  document.querySelector('.mission').scrollIntoView({ behavior:'smooth' });
}

// Shrinking header on scroll
const header = document.querySelector('header');
function checkScroll() {
  if(window.scrollY > 50) header.classList.add('scrolled');
  else header.classList.remove('scrolled');
}
window.addEventListener('scroll', checkScroll);
window.addEventListener('load', checkScroll);
