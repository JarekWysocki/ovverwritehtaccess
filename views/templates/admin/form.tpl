{if $htpasswdExist}
<h1>Uwaga! Hasło zostało już nadane - jeśli wpiszesz je ponownie - zmienisz je. Powiadom osoby, które trzeba.</h1>
{/if}
<form action="" method="post">
    Wpisz nazwę uzytkownika: <input type="text" name="htaccessName"><br><br>
    Wpisz hasło: <input type="text" name="htaccessPassword"><br><br>
    <input type="submit">
</form>