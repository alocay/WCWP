var lock = null;
$(document).ready(function() {
	$("#steam-name-submit").click(function() {
		var steamName = $("#steam-name").val();
		
		$.get("php/wcwp.php", { name: steamName })
	   		.done(function (data) {
	   			$("#nick").text(data);
	   		})
	   		.fail(function (xhr, err) {
	   			alert(err);
	   		})
	});
   
});