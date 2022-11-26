## Features
### General Secure Web
- Registration/Login System
  - Username (unique)
  - Password
  - Email
  - Phone Number
- All the values of the account (with the exception of the username) are changable.
- All User data is stored in a database (MySQL)
- All data store is encrypted with AES, and only visible to user.
- XSS secure.
- CSRF secure.
- SQLi secure.

### Secure Login Feature
- Activation of account required. 
  - Email sent to the registered email.
  - Account inaccessible unless activated via email.
- Three distinct security questions are asked and stored (Salted and Hashed).
  - Needed to change informations of the account. (password, email, phone number)
- Two Factor Authentication can be activated (and deactivated).
- Captcha added for login page. (Botnet Attack)
- Number of attempts added.
  - If a user tries to enter into an account more than three times, the ip is stored in database and they will have to wait for 10 minutes.
- XSS secure.
- CSRF secure.
- SQLi secure.

### Password Strengthening Features
- Password entropy.
  - At least one lowercase letter.
  - At least one highercase letter.
  - At least one number.
  - At least one special character.
  - Password has to be longer than eight characters.
- Password is Salted and Hashed using latest algorithms.
- Password is recoverable via email.
  - 5 hour limit.
- XSS secure.
- CSRF secure.
- SQLi secure.

### Request Evaluation
- Users can send evaluations, consisting of: Title of evaluation, Comment of evaluation.
- Drop down box included to select best choice of contact (email or phone number).
- The data is then encrypted, and only administrators will be able to view the encrypted content.
- XSS secure.
- CSRF secure.
- SQLi secure.

### Photo Upload for Evaluations
- Alongside the title and comment, of an evaluation, a photo file can also be uploaded.
- The file is stored in the server, and the url to the file is stored in a database.
- The file itself is encrypted, and is decrypted when viewing is requested.
- XSS secure.
- CSRF secure.
- SQLi secure.

### Administrator Role
- Administration roles are added to the website.
- Administators are able to view a list of evaluations.
- From the list of evaluations, they are able to contact the user who sent the request, or delete the request.
- They are also able to see all the information of an evaluation (title, comment, photo).
- XSS secure.
- CSRF secure.
- SQLi secure.