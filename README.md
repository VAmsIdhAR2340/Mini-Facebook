As the name suggests, this is called a mini facebook, it allows users to uplod posts and, the friends of the users can view the posts.  

In this project, I used prepared statement, input sanitization, and haven’t trusted the user that the user will give proper inputs in 
the input fields. By keeping this mind, I prepared my code against XSS, SQLi, CSRF attacks. 

Multiple layers of security measures are implemented, such as input validation, prepared statements, and session management, ensuring 
that if one layer is breached, there are  additional layers to mitigate the risk.  Security measures are implemented across various 
components of the application, including user authentication, database interactions, and data validation, ensuringcomprehensive 
coverage against potential vulnerabilities.

To safeguard against XSS, input validation and output encoding are applied, ensuring user-supplied data is sanitized before rendering 
to prevent script injection. SQL injection risks are reduced using parameterized queries, differentiating user input from SQL code to 
remove malicious injection attempts. CSRF protection is enforced by including unique CSRF tokens in form submissions, validating them 
on the server to prevent unauthorized actions initiated by malicious sites. Session hijacking threats are addressed through secure 
session management practices, including session regeneration post-login and HTTPS encryption to safeguard data transmission. 
Additionally, strong password hashing, role-based access control, and ensuring robust protection of user data and system integrity.

