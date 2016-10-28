<html>
<head>
<title>Тестовое задание, geo+php+mysql</title>
<meta charset="utf-8">


<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.1/jquery.min.js"></script>

</head>
<body>

<h1>Особенности реализации</h1>
Проверка данных <b>только</b> на уровне заполненности необходимых полей (на стороне клиента и сервера), <b>формат данных не проверяется</b>.<br>
Вместо фреймворка использовался класс для REST-API url-разметки и собственный db-коннектор из другого проекта.<br>
В качестве критерия определения, зарегистрирован ли пользователь на событие используется его email.<br>
При регистрации на второе и более событие другие поля, (вроде ФИО и тд) пользователя в БД не меняются (иначе их нужно отнести к свойствам регистрации на событие).<br>
Для удобства скрипты встроены в тело страницы.<br><br>
<b>API</b> вместо apiary.io <br>
<b>/v1/events/</b> - GET-запросом получаем список доступных событий (в данном случае API не делает запрос в базу, а генерирует массив из выбранных мной названий событий с Афиши);<br>
<b>/v1/events/{id}</b> - POST-запросом с полями из формы ниже регистрирует пользователя на событие с идентификатором {id}
<b>/v1/users/</b> - GET-запросом получаем список всех пользователей;<br>


<h1>Регистрация пользователя</h1>
<form id="register_user_form" method="POST">
	<p><label for="field_name">ФИО * <input required type="text" name="user_name" id="field_name" placeholder="Иванов Пётр Сидорович"></label></p>
	<p><label for="field_mail">email * <input required type="text" name="user_email" id="field_mail" placeholder="mail@mail.com"></label></p>
	<p><label for="field_phone">телефон <input type="text" name="user_phone" id="field_phone" placeholder="+70000000000"></label></p>
	<p><label for="field_sex">пол <select name="user_sex" id="field_sex"><option value="0">не указан</option><option value="1">мужской</option><option value="3">женский</option></select></label></p>
	<p><label for="field_bday">Дата рождения <input type="text" name="user_bday" id="field_bday" placeholder="дд.мм.гггг"></label></p>
	<p><label for="field_event">Событие * <select required name="user_event" id="field_event"><option value="">не указан</option></select></label></p>
	<p><input type="submit"></p>
</form>

<h1>Список пользователей</h1>
<a href="#" id="get_userlist">Получить список</a>
<div id="user_list">
	<table>
	<thead>
		<th>id</th>
		<th>ФИО</th>
		<th>Email</th>
		<th>Телефон</th>
		<th>Пол</th>
		<th>Дата рождения</th>
	</thead>
	<tbody>
	</tbody>
	</table>

</div>


<script>


(function($){

function HtmlEncode(value){
  return $('<div/>').text(value).html();
}

$(function(){
			 $.ajax("/v1/events/",{
				method:"GET",
				dataType:"json",
				success:function(data){
					if(data["errors"].length>0) throw new Error(data["errors"][0]);
					else for(var i in data["response"]){
						var p_obj=data["response"][i];
						$("<option value='"+p_obj.id+"'>"+p_obj.event_name+"</option>").appendTo("#register_user_form select[name='user_event']");
					}
				}
			});
			
			$("a#get_userlist").click(function(e){
				e.preventDefault();	
			 	$.ajax("/v1/users/",{
					method:"GET",
					dataType:"json",
					success:function(data){
						$("#user_list table tbody").html("");
						if(data["errors"].length>0) throw new Error(data["errors"][0]);
						else for(var i in data["response"]){
							var p_obj=data["response"][i];
$("<tr><td>"+HtmlEncode(p_obj.user_id)+"</td><td>"+HtmlEncode(p_obj.user_name)+"</td><td>"+HtmlEncode(p_obj.user_email)+"</td><td>"+HtmlEncode(p_obj.user_phone)+"</td><td>"+(p_obj.user_sex==0?"не указан":(p_obj.user_sex==1?"мужской":"женский"))+"</td><td>"+HtmlEncode(p_obj.user_bday)+"</td></tr>").appendTo("#user_list table tbody");
						}
					}
				});
			});
		

		$('#register_user_form').submit(
		function(e){
		e.preventDefault();
		
			var has_empty = false;
			$(this).find('input[required]').each(function () {
			if (!$(this).val()) {
				has_empty=true;
				return false;
				}
	  	 	});

			if (has_empty) {
   				alert('Пожалуйста, заполните все поля формы.');
   				return false;
   				}
			
			var event_id=parseInt($(this).find("select[name='user_event']").val());
			if(!event_id){
					throw new Error("Не выбрано событие");
					alert('Пожалуйста, выберите событие.');
				}
			
			 $.ajax("/v1/events/"+event_id,{
				method:"POST",
				dataType:"json",
				data: $('#register_user_form').serialize(),
				success:function(data){
					if(data["errors"].length>0) throw new Error(data["errors"][0]);
					else {
						alert("Спасибо за регистрацию");
						p_obj=data["response"];						
					}
				}
			});
		}
);

});})(jQuery)
</script>

</body>
</html>