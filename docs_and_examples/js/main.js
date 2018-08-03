/* Loop through all dropdown buttons to toggle between hiding and showing its dropdown content - This allows the user to have multiple dropdowns without any conflict */
var dropdown = document.getElementsByClassName("dropdown-btn");
var i;

for (i = 0; i < dropdown.length; i++) {
  dropdown[i].addEventListener("click", function() {
    this.classList.toggle("active");
    var dropdownContent = this.nextElementSibling;
    if (dropdownContent.style.display === "block") {
      dropdownContent.style.display = "none";
    } else {
      dropdownContent.style.display = "block";
    }
  });
}


function ExpandDropDown(id)
{
   var dropdownBtn = document.getElementById(id);
   var dropdownContent = dropdownBtn.nextElementSibling;
   dropdownContent.style.display = "block";
}


Prism.plugins.NormalizeWhitespace.setDefaults({
	'remove-trailing': true,
	'remove-indent': true,
	'left-trim': true,
	'right-trim': true,
// 	'break-lines': 80,
// 	'indent': 2,
	'remove-initial-line-feed': false,
// 	'tabs-to-spaces': 4,
// 	'spaces-to-tabs': 4
});