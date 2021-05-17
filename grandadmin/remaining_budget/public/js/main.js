const showNavBar = (toggleId, navId, bodyId, headerId) => {
  const toggle = document.getElementById(toggleId);
  const nav = document.getElementById(navId);
  const bodypd = document.getElementById(bodyId);
  const headerpd = document.getElementById(headerId);
  const shortLogo = document.getElementById('nav_logo_short');
  const fullLogo = document.getElementById('nav_logo_full');
  console.log(toggle, nav, bodypd, headerpd);
  if (toggle && nav && bodypd && headerpd) {
    toggle.addEventListener("click", () => {
      // show navbar
      nav.classList.toggle("nav-show");
      // change icon
      toggle.classList.toggle("bx-x");
      toggle.classList.toggle("bxs-right-arrow");
      toggle.classList.toggle("bxs-left-arrow");
      // add padding to body
      bodypd.classList.toggle("body-pd");
      // add padding to header
      headerpd.classList.toggle("body-pd");

      var checkNavShow = false;
      nav.classList.forEach(function(cl) {
        if (cl === 'nav-show') {
          checkNavShow = true;
        }
      })

      if (checkNavShow) {
        shortLogo.style.display = "none";
        fullLogo.style.display = "block";
      } else {
        shortLogo.style.display = "block";
        fullLogo.style.display = "none";
      }
    });
  }
};

showNavBar("header-toggle", "nav-bar", "body-pd", "header");

// Link Active
const linkColor = document.querySelectorAll(".nav__link");

function colorLink() {
  if (linkColor) {
    linkColor.forEach((l) => l.classList.remove("active"));
    this.classList.add("active");
  }
}

linkColor.forEach((l) => l.addEventListener("click", colorLink));

function currencyFormat(num) {
  return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
}

function stringNullToDash(text){
  if(typeof text !== 'undefined' && text){
    return text
  }else{
    return "-"
  }
}
