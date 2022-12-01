## Features
### General Secure Web
- Registration/Login System
  - Username (unique)
  - Password
  - Email
  - Phone Number
- All the values of the account (with the exception of the username) are changable.
- All User data is stored in a database (MySQL)
- All the sensitive data the user provides are stored encrypted (email, phone number, password)
  - Specifically the email and phone number, they are stored via a symmetrical encryption method. This method utilises the password of the user as its key. This allows the information of the user to be only visible by them.
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
  - Once the 10 minutes are passed, when the user tries to insert a password again, the ip is removed from the database, and the user has three more chances.
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
  - This is done via creating a public/private key for every administrator. All sensitive information in the evaluation (contact method, body of evaluation, pictures) are encrypted via a symmetric encryption. The key used to encrypt the data is then encrypted with the public key of all admins (thus if there are 5 admins, 5 sets of the keys, with different encryptions, are stored in the database). The private key is also stored in the database, but it is encrypted with the password of the admin's account. The public key is stored as is. When a admin wishes to view the list of evaluations, the private key is decrypted with the admin password, the private key is used to decrypt the key for the evaluation, and the decrypted key is used to decrypt the evaluation. This creates a secure system which allows multiple admins to see the same information, while mainting a level of security.
- XSS secure.
- CSRF secure.
- SQLi secure.

### Photo Upload for Evaluations
- Alongside the title and comment, of an evaluation, a photo file can also be uploaded.
- The file is stored in the server, and the url to the file is stored in a database.
- File name is changed to random values. 
- The files themselves are encrypted. The encryption follows the scheme that has been explained earlier.
- XSS secure.
- CSRF secure.
- SQLi secure.

### Administrator Role
- Administration roles are added to the website.
- Administators are able to view a list of evaluations.
- Administrators have the ability to give the role of administrator to other users in the database.
  - Giving another user the administrator role will create a public/private key for the selected user.
- From the list of evaluations, they are able to see the preferred method of, or delete the request.
- They are also able to see all the information of an evaluation (title, comment, photo).
- XSS secure.
- CSRF secure.
- SQLi secure.