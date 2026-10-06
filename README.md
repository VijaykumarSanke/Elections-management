# College Elections Management System

A web-based voting system for college elections, built with PHP and MySQL. Only eligible students can register, email OTP verification confirms their identity, and each voter can cast one vote across the election categories.

## Features

- **Eligibility check** – registration is limited to students listed in the `students` table. The roll-number format is validated, and the name, email and EduPrime password must match the student records.
- **Email OTP verification** – a one-time code is sent by email (PHPMailer over SMTP/STARTTLS). Codes are 6 digits and expire after 10 minutes. Resending is supported.
- **Voter ID and password login** – each registered student receives a 5-digit voter ID. Passwords are hashed with `password_hash()` and checked with `password_verify()`. Sessions keep users logged in.
- **One vote per voter** – before inserting votes, the system checks whether the user has already voted. Votes are recorded for three categories: Sports Incharge, Co-Curricular Activities Incharge and General Activity Incharge.
- **Results page** – vote counts per candidate and the leading candidate(s) for each category. The results page is protected by an admin password.
- **Voter ID recovery** – a forgot-voter-ID flow.
- **SQL injection protection** – prepared statements are used for database queries.
- **Input validation** – on both the client and the server.

## Tech Stack

| Layer | Technology |
|---|---|
| Frontend | HTML, CSS, JavaScript |
| Backend | PHP |
| Database | MySQL |
| Email | PHPMailer (Composer) |
| Server | Apache (XAMPP) |

## Project Structure

```
Elections-management/
├── index.html                    # Landing page
├── register.html / register.php  # Registration form and validation
├── send_otp.php                  # Eligibility check + sends OTP email
├── verify_otp.html               # OTP entry page
├── verify_otp_process.php        # Verifies the OTP
├── resend_otp.php                # Resends the OTP
├── otp_functions.php             # OTP generation, storage, email sending
├── email_config.php              # SMTP and OTP settings (do not commit real credentials)
├── login.html / login.php        # Voter login
├── election.html / vote.php      # Voting page and vote submission
├── view_results.html             # Admin password form for results
├── verify_password.php           # Checks the admin password
├── results.php                   # Results per category
├── forgot_voter_id.html / .php   # Voter ID recovery
├── registration_confirmation.php # Shows the voter ID after registration
├── config.php                    # Timezone and DB connection helper
├── db.txt                        # Base database schema
└── composer.json                 # PHPMailer dependency
```

## Getting Started

1. Clone the repository into your XAMPP `htdocs` folder:
   ```bash
   git clone https://github.com/VijaykumarSanke/Elections-management.git
   ```
   For example: `C:/xampp/htdocs/Elections-management/`
2. Install dependencies (skip if the `vendor/` folder is present):
   ```bash
   composer install
   ```
3. Start **Apache** and **MySQL** in XAMPP.
4. Create the database and tables by running the SQL in `db.txt`, then add the extra table and column the code also needs:
   ```sql
   USE secure_elections;

   ALTER TABLE students ADD COLUMN eduprime_password VARCHAR(255);

   CREATE TABLE otp_verifications (
       id INT AUTO_INCREMENT PRIMARY KEY,
       email VARCHAR(255) NOT NULL,
       otp VARCHAR(10) NOT NULL,
       expires_at DATETIME NOT NULL,
       verified BOOLEAN DEFAULT FALSE,
       created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
   );
   ```
5. Add your eligible students to the `students` table.
6. Open `email_config.php` and set your SMTP host, username, password and sender details. Use an app password for Gmail, and never commit real credentials.
7. Open http://localhost/Elections-management/

## How It Works

1. **Register** – the student enters name, roll number, email and passwords. The system checks eligibility against the `students` table.
2. **Verify** – an OTP is emailed. After the student enters it correctly, registration is completed and a voter ID is issued.
3. **Log in** – the student logs in with the voter ID and password.
4. **Vote** – the student selects one candidate in each category and submits. A second attempt is rejected.
5. **Results** – an admin enters the results password to view the counts.

## Limitations

- Candidate names are defined in the code rather than managed from an admin screen.
- There is a single admin password for results and no full admin panel.
- Voter IDs are 5 digits, so the system is suited to a college-scale election.
- The project is for learning and has not been through a security audit.

## Future Improvements

- Admin panel for managing candidates, voters and election dates
- Stronger voter ID generation and OTP attempt limits
- Automated tests

## Author

Sanke Vijaykumar – [GitHub](https://github.com/VijaykumarSanke) · vijaykumar.sanke7@gmail.com
