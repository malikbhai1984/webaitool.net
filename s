* { margin: 0; padding: 0; box-sizing: border-box; }

body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background: #f4f7f6;
    min-height: 100vh;
    display: flex;
    flex-direction: column;
}

/* Navigation */
nav {
    background: white;
    padding: 18px 5%;
    display: flex;
    justify-content: space-between;
    align-items: center;
    box-shadow: 0 2px 15px rgba(0,0,0,0.08);
    position: sticky;
    top: 0;
    z-index: 100;
}

.logo {
    font-size: 26px;
    font-weight: 800;
    color: #6c5ce7;
    text-decoration: none;
}

.menu-list {
    display: flex;
    gap: 25px;
    position: absolute;
    left: 50%;
    transform: translateX(-50%);
}

.menu-list a {
    text-decoration: none;
    color: #333;
    font-weight: 600;
    font-size: 15px;
}

.menu-list a:hover {
    color: #6c5ce7;
}

/* Dropdown Menu Styles */
.dropdown {
    position: relative;
    display: inline-block;
}

.dropbtn {
    text-decoration: none;
    color: #333;
    font-weight: 600;
    font-size: 15px;
    cursor: pointer;
}

.dropdown-content {
    display: none;
    position: absolute;
    background-color: white;
    min-width: 200px;
    box-shadow: 0 8px 16px rgba(0,0,0,0.1);
    z-index: 1;
    border-radius: 8px;
    margin-top: 5px;
}

.dropdown-content a {
    color: #333;
    padding: 12px 16px;
    text-decoration: none;
    display: block;
    font-size: 14px;
}

.dropdown-content a:hover {
    background-color: #f8f9fa;
    color: #6c5ce7;
}

/* Keep dropdown open on hover AND click */
.dropdown:hover .dropdown-content {
    display: block;
}

.dropdown.active .dropdown-content {
    display: block;
}

.dropdown:hover .dropbtn {
    color: #6c5ce7;
}

/* Hero Section */
.hero {
    background: linear-gradient(135deg, #6c5ce7, #a29bfe);
    color: white;
    padding: 80px 20px;
    text-align: center;
}

.hero h1 {
    font-size: 48px;
    margin-bottom: 15px;
}

.hero p {
    font-size: 20px;
    opacity: 0.95;
}

/* Tools Section */
.tools-section {
    max-width: 1200px;
    margin: -40px auto 60px;
    padding: 0 20px;
}

.section-title {
    text-align: center;
    font-size: 32px;
    color: #2d3436;
    margin-bottom: 40px;
}

.tools-grid {
    display: grid;
    gap: 25px;
    margin-bottom: 40px;
}

.tool-card {
    background: white;
    padding: 35px 25px;
    border-radius: 18px;
    text-align: center;
    box-shadow: 0 5px 20px rgba(0,0,0,0.08);
    transition: all 0.3s ease;
    border: 2px solid transparent;
}

.tool-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 10px 30px rgba(108,92,231,0.2);
    border-color: #6c5ce7;
}

.tool-icon {
    font-size: 48px;
    margin-bottom: 15px;
}

.tool-card h3 {
    font-size: 22px;
    margin-bottom: 12px;
    color: #2d3436;
}

.tool-card p {
    color: #666;
    margin-bottom: 20px;
    line-height: 1.6;
    font-size: 14px;
}

.tool-btn {
    display: inline-block;
    padding: 12px 28px;
    background: #6c5ce7;
    color: white;
    text-decoration: none;
    border-radius: 10px;
    font-weight: 700;
    transition: all 0.3s;
}

.tool-btn:hover {
    background: #5f4dd1;
    transform: scale(1.05);
}

/* Footer */
footer {
    background: #2d3436;
    color: white;
    text-align: center;
    padding: 40px 20px;
    margin-top: auto;
}

footer p {
    font-size: 15px;
}

/* Container for tool pages */
.container {
    max-width: 1200px;
    margin: -30px auto 50px;
    padding: 20px;
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
    gap: 25px;
}

/* Input Styles */
input[type="text"],
input[type="email"],
input[type="number"],
input[type="file"],
textarea,
select {
    width: 100%;
    padding: 12px;
    margin: 8px 0;
    border: 2px solid #e0e0e0;
    border-radius: 8px;
    font-size: 14px;
}

input:focus, textarea:focus, select:focus {
    outline: none;
    border-color: #6c5ce7;
}

button {
    background: #6c5ce7;
    color: white;
    border: none;
    padding: 12px 25px;
    margin: 5px 5px 5px 0;
    border-radius: 8px;
    cursor: pointer;
    font-size: 14px;
    font-weight: 600;
}

button:hover {
    background: #5f4dd1;
}

.result {
    margin-top: 20px;
    padding: 20px;
    background: #f8f9fa;
    border-radius: 10px;
    border-left: 4px solid #6c5ce7;
}

/* Responsive */
@media (max-width: 768px) {
    .hero h1 { font-size: 32px; }
    .hero p { font-size: 16px; }
    .menu-list { flex-direction: column; gap: 10px; }
    .container { grid-template-columns: 1fr; }
}
