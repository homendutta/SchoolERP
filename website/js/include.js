Promise.all([
  fetch("../includes/header.html").then((res) => res.text()),
  fetch("../includes/footer.html").then((res) => res.text()),
]).then(([header, footer]) => {
  document.getElementById("header-placeholder").innerHTML = header;
  document.getElementById("footer-placeholder").innerHTML = footer;

  // Load menu scripts AFTER header exists
  const script1 = document.createElement("script");
  script1.src = "../assets/js/vendor/bootsnav.js";

  script1.onload = function () {
    const script2 = document.createElement("script");
    script2.src = "../assets/js/vendor/modern_megamenu.js";

    script2.onload = function () {
      const script3 = document.createElement("script");
      script3.src = "../assets/js/app.js";
      document.body.appendChild(script3);
    };

    document.body.appendChild(script2);
  };

  document.body.appendChild(script1);
});
