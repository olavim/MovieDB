# MovieDB
A sleek, mobile friendly web app intended to be used as a movie database.

The hierarchy assumes that the directory `public_html` is the document root. The directory `include` (outside document root) contains functions global to the whole website that are not supposed to be accessed by URL. Keep your sensitive data there.

I have omitted `secure.php` from my commits as it contains solely personal information. Right now that file contains my database user password and *secret* that is used to identify GitHub's webhooks.

## Connection

The app relies on two databases: **secure_login**, which holds user data (usernames, passwords, attempted logins, etc.), and the actual movie database **moviedb**. The sql for setting up these two databases can be found under the names of secure_login.sql and moviedb.sql.
