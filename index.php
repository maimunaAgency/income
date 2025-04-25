<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MZ Income - লগইন এবং সাইনআপ</title>
    <link href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Hind Siliguri', sans-serif;
        }

        body {
            background: #121212;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            color: #fff;
        }

        .container {
            background-color: #1e1e1e;
            border-radius: 10px;
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.3);
            width: 100%;
            max-width: 400px;
            overflow: hidden;
            border: 1px solid #333;
        }

        .tabs {
            display: flex;
            background-color: #000;
            border-bottom: 2px solid #FFDE03;
        }

        .tab {
            flex: 1;
            padding: 15px;
            text-align: center;
            font-weight: 500;
            color: #FFDE03;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .tab.active {
            background-color: #FFDE03;
            color: #000;
            font-weight: 600;
        }

        .form-container {
            padding: 30px;
        }

        .input-group {
            margin-bottom: 20px;
        }

        .input-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #FFDE03;
        }

        .input-group input {
            width: 100%;
            padding: 12px 15px;
            border-radius: 5px;
            border: 1px solid #333;
            font-size: 14px;
            transition: all 0.3s ease;
            background-color: #2a2a2a;
            color: white;
        }

        .input-group input:focus {
            outline: none;
            border-color: #FFDE03;
            box-shadow: 0 0 0 2px rgba(255, 222, 3, 0.3);
        }

        .input-group input::placeholder {
            color: #888;
        }

        .btn-submit {
            width: 100%;
            background: #FFDE03;
            color: #000;
            border: none;
            padding: 14px;
            border-radius: 5px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-transform: uppercase;
        }

        .btn-submit:hover {
            background: #FFE838;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(255, 222, 3, 0.3);
        }

        .btn-submit:active {
            transform: translateY(0);
        }

        .forgot-password {
            text-align: center;
            margin-top: 15px;
        }

        .forgot-password a {
            color: #FFDE03;
            text-decoration: none;
            font-size: 14px;
        }

        .forgot-password a:hover {
            text-decoration: underline;
        }

        .signup-form {
            display: none;
        }

        .message {
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 5px;
            text-align: center;
        }

        .error {
            background-color: rgba(220, 53, 69, 0.2);
            color: #ff6b6b;
            border: 1px solid rgba(220, 53, 69, 0.3);
        }

        .success {
            background-color: rgba(40, 167, 69, 0.2);
            color: #51cf66;
            border: 1px solid rgba(40, 167, 69, 0.3);
        }

        .logo {
            text-align: center;
            margin-bottom: 30px;
        }

        .logo h1 {
            color: #FFDE03;
            font-weight: 700;
            font-size: 28px;
            letter-spacing: 1px;
            text-transform: uppercase;
        }

        .logo span {
            display: block;
            font-size: 14px;
            color: #aaa;
            margin-top: 5px;
            letter-spacing: 1px;
        }

        .account-info {
            text-align: center;
            margin-top: 20px;
            padding: 20px 15px;
            background-color: rgba(255, 222, 3, 0.1);
            border-radius: 5px;
            display: none;
            border: 1px solid #FFDE03;
        }

        .account-info h3 {
            margin-bottom: 15px;
            color: #FFDE03;
            font-size: 18px;
        }

        .account-number {
            font-size: 28px;
            font-weight: 700;
            color: white;
            letter-spacing: 3px;
            background: #000;
            padding: 10px;
            border-radius: 5px;
            display: inline-block;
            margin: 5px 0 15px;
        }

        .form-footer {
            font-size: 12px;
            color: #aaa;
            text-align: center;
            margin-top: 20px;
            line-height: 1.6;
        }

        @media (max-width: 480px) {
            .container {
                max-width: 100%;
            }
            
            .form-container {
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="tabs">
            <div class="tab active" id="login-tab">লগইন</div>
            <div class="tab" id="signup-tab">রেজিস্ট্রেশন</div>
        </div>
        
        <div class="form-container">
            <div class="logo">
                <h1>MZ Income</h1>
                <span>আপনার আয়ের নতুন দিগন্ত</span>
            </div>
            
            <div id="message-container"></div>
            
            <div id="account-info-container" class="account-info">
                <h3>আপনার অ্যাকাউন্ট নম্বর</h3>
                <div class="account-number" id="account-number-display"></div>
                <p>এই নম্বরটি সংরক্ষণ করুন। আপনি এটি দিয়ে লগইন করতে পারবেন।</p>
            </div>
            
            <!-- Login Form -->
            <form id="login-form" class="login-form">
                <div class="input-group">
                    <label for="login-username">ইউজারনেম অথবা অ্যাকাউন্ট নম্বর</label>
                    <input type="text" id="login-username" name="username" required placeholder="ইউজারনেম বা ৬ সংখ্যার অ্যাকাউন্ট নম্বর">
                </div>
                
                <div class="input-group">
                    <label for="login-password">পাসওয়ার্ড</label>
                    <input type="password" id="login-password" name="password" required placeholder="আপনার পাসওয়ার্ড">
                </div>
                
                <button type="submit" class="btn-submit">লগইন করুন</button>
                
                <div class="forgot-password">
                    <a href="#">পাসওয়ার্ড ভুলে গেছেন?</a>
                </div>
            </form>
            
            <!-- Signup Form -->
            <form id="signup-form" class="signup-form">
                <div class="input-group">
                    <label for="signup-username">ইউজারনেম</label>
                    <input type="text" id="signup-username" name="username" required placeholder="একটি ইউজারনেম বাছাই করুন">
                </div>
                
                <div class="input-group">
                    <label for="signup-password">পাসওয়ার্ড</label>
                    <input type="password" id="signup-password" name="password" required placeholder="একটি পাসওয়ার্ড তৈরি করুন">
                </div>
                
                <div class="input-group">
                    <label for="signup-confirm-password">পাসওয়ার্ড নিশ্চিত করুন</label>
                    <input type="password" id="signup-confirm-password" name="confirm_password" required placeholder="পাসওয়ার্ড পুনরায় লিখুন">
                </div>
                
                <div class="input-group">
                    <label for="signup-mobile">মোবাইল নম্বর</label>
                    <input type="tel" id="signup-mobile" name="mobile" required placeholder="আপনার মোবাইল নম্বর লিখুন">
                </div>
                
                <div class="input-group">
                    <label for="signup-referral">রেফারেল কোড (ঐচ্ছিক)</label>
                    <input type="text" id="signup-referral" name="referral" placeholder="রেফারেল কোড যদি থাকে">
                </div>
                
                <button type="submit" class="btn-submit">রেজিস্ট্রেশন করুন</button>
            </form>
            
            <div class="form-footer">
                MZ Income এ রেজিস্ট্রেশন করে আপনি আমাদের <a href="#" style="color: #FFDE03;">শর্তাবলী</a> মেনে নিচ্ছেন
            </div>
        </div>
    </div>

    <script>
        // Tab switching
        const loginTab = document.getElementById('login-tab');
        const signupTab = document.getElementById('signup-tab');
        const loginForm = document.getElementById('login-form');
        const signupForm = document.getElementById('signup-form');
        const messageContainer = document.getElementById('message-container');
        const accountInfoContainer = document.getElementById('account-info-container');
        const accountNumberDisplay = document.getElementById('account-number-display');

        loginTab.addEventListener('click', () => {
            loginTab.classList.add('active');
            signupTab.classList.remove('active');
            loginForm.style.display = 'block';
            signupForm.style.display = 'none';
            accountInfoContainer.style.display = 'none';
            messageContainer.innerHTML = '';
        });

        signupTab.addEventListener('click', () => {
            signupTab.classList.add('active');
            loginTab.classList.remove('active');
            signupForm.style.display = 'block';
            loginForm.style.display = 'none';
            accountInfoContainer.style.display = 'none';
            messageContainer.innerHTML = '';
        });

        // Form validation and submission
        loginForm.addEventListener('submit', (e) => {
            e.preventDefault();
            
            const username = document.getElementById('login-username').value;
            const password = document.getElementById('login-password').value;
            
            if (!username || !password) {
                showMessage('সমস্ত ফিল্ড পূরণ করুন', 'error');
                return;
            }
            
            // AJAX request to login.php
            const formData = new FormData();
            formData.append('user_input', username);
            formData.append('password', password);
            
            fetch('login.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showMessage('লগইন সফল! রিডাইরেক্ট হচ্ছে...', 'success');
                    setTimeout(() => {
                        window.location.href = 'dashboard.php';
                    }, 1500);
                } else {
                    showMessage(data.message === 'Invalid password' ? 'ভুল পাসওয়ার্ড' : 
                               (data.message === 'Username not found' ? 'ইউজারনেম পাওয়া যায়নি' : 
                               (data.message === 'Account number not found' ? 'অ্যাকাউন্ট নম্বর পাওয়া যায়নি' : 'অবৈধ লগইন তথ্য')), 'error');
                }
            })
            .catch(error => {
                showMessage('একটি ত্রুটি ঘটেছে। আবার চেষ্টা করুন।', 'error');
                console.error('Error:', error);
            });
        });

        signupForm.addEventListener('submit', (e) => {
            e.preventDefault();
            
            const username = document.getElementById('signup-username').value;
            const password = document.getElementById('signup-password').value;
            const confirmPassword = document.getElementById('signup-confirm-password').value;
            const mobile = document.getElementById('signup-mobile').value;
            const referral = document.getElementById('signup-referral').value;
            
            if (!username || !password || !confirmPassword || !mobile) {
                showMessage('সমস্ত প্রয়োজনীয় ফিল্ড পূরণ করুন', 'error');
                return;
            }
            
            if (password !== confirmPassword) {
                showMessage('পাসওয়ার্ড মিলছে না', 'error');
                return;
            }
            
            // Validate mobile number
            const mobileRegex = /^\d{10,11}$/;
            if (!mobileRegex.test(mobile)) {
                showMessage('একটি বৈধ ১০ বা ১১ সংখ্যার মোবাইল নম্বর দিন', 'error');
                return;
            }
            
            // AJAX request to signup.php
            const formData = new FormData();
            formData.append('username', username);
            formData.append('password', password);
            formData.append('mobile', mobile);
            formData.append('referral', referral);
            
            fetch('signup.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showMessage('রেজিস্ট্রেশন সফল হয়েছে!', 'success');
                    
                    // Display account number
                    accountNumberDisplay.textContent = data.account_number;
                    accountInfoContainer.style.display = 'block';
                    signupForm.style.display = 'none';
                    
                    // Redirect after delay
                    setTimeout(() => {
                        window.location.href = 'dashboard.php';
                    }, 8000);
                } else {
                    let errorMessage = 'রেজিস্ট্রেশন ব্যর্থ হয়েছে। আবার চেষ্টা করুন।';
                    
                    if (data.message === 'Username already exists') {
                        errorMessage = 'এই ইউজারনেম ইতিমধ্যে ব্যবহৃত হয়েছে';
                    } else if (data.message === 'Mobile number already registered') {
                        errorMessage = 'এই মোবাইল নম্বর ইতিমধ্যে নিবন্ধিত আছে';
                    } else if (data.message === 'Invalid referral code') {
                        errorMessage = 'অবৈধ রেফারেল কোড';
                    }
                    
                    showMessage(errorMessage, 'error');
                }
            })
            .catch(error => {
                showMessage('একটি ত্রুটি ঘটেছে। আবার চেষ্টা করুন।', 'error');
                console.error('Error:', error);
            });
        });

        function showMessage(message, type) {
            messageContainer.innerHTML = `<div class="message ${type}">${message}</div>`;
        }
    </script>
</body>
</html>