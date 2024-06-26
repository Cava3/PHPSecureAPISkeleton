# PHPSecureAPISkeleton
A simple skeleton for an SID secured API made in vanilla PHP.  
Allows for simple server sided API creation with a secure session and account creation system.

**Be careful, this skeleton does not ensure the content you add to it is secure.**  
**Please ensure you use correctly the functions that are provided here AND your own code is """safe""".**

# Everything is WIP !
# Do not use now !

---
## How to use
1. Download the zip file (or remove the .git folder after cloning)
2. Put it in your exposed php server (local or docker. Docker compose + dockerfile provided lower)
3. Open your browser to make sure it works (`<ip>/utils/endpoint.php` should return a 602 error in json)
4. Start coding your branches as you like by creating folders in the root directory
5. Make sure to use `/utils/endpoint.php` -> `beginEndpoint()` for every endpoint that requires the user to be logged on

---
## Security Features
- **SID**: A session ID is generated at each login.
  - Prevents password stealing in console/cookies/mitm if client application is not stoopid (do not store password in client 👀)
  - Prevents token stealing (common Discord issue)
  - Newer session invalidates older session, to allow user to disconnect a potential SID hijacker
  - Invalidates session after 1 hour of inactivity, to prevent long term hijacking (again common Discord issue)
  - *Remember to call the `beginEndpoint()` function in your endpoints as it also updates the SID invalidation time*
- **Hashing and Salting**: Passwords are hashed and salted server-side.
  - Hashing forces hacker to brute force the password in case of a data leak
  - Salting forces the hacker to start from scratch for each password, and prevents the use of rainbow tables
  - Slight delay on each API call to slow down a lot the brute forcing process (both password and SID). Low enough to not be noticeable by the user, high enough to be a pain for the hacker
  - *It is recommended to also salt and hash client-side using the username (or a derived value) to prevent MITM attacks*
<!-- TODO -->
- **Rate Limiting**: A rate limit is set on the API to prevent brute forcing.
  - 3 login/register requests per minute per IP
  - 5 other requests per second per IP
- **Error Handling**: Errors are handled in a way that does not leak information.
  - All custom errors are returned as a HTTP 6xx error in json format
  - The error message is not detailed to prevent information leaks (unlike "password incorrect" or "username already taken")
- **Account Creation/Connection**: Account creation is secured.
  - Username and password are checked for length and characters
  - Username and passwords are non-unique, but the combination of both is (allows to remove the "username already taken" error message, which is an info leak + allows for multiple accounts with the same username)