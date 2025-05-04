<?php include 'includes/header.php'; ?>

<!-- Hero Section -->
<section class="hero-section position-relative">
    <div id="heroCarousel" class="carousel slide carousel-fade" data-bs-ride="carousel">
        <div class="carousel-inner">
            <div class="carousel-item active" style="background-image: url('/vet_anywhere/assets/images/hero-1.jpg');">
                <div class="carousel-caption text-start">
                    <h1 class="display-4 fw-bold animate__animated animate__fadeInDown">Vet Anywhere</h1>
                    <p class="lead animate__animated animate__fadeInUp animate__delay-1s">Complete pet health management at your fingertips</p>
                    <?php if (!isLoggedIn()): ?>
                        <div class="mt-4 animate__animated animate__fadeInUp animate__delay-2s">
                            <a href="auth/register.php" class="btn btn-primary btn-lg rounded-pill px-4 me-3">
                                <i class="fas fa-user-plus me-2"></i> Get Started
                            </a>
                            <a href="auth/login.php" class="btn btn-outline-light btn-lg rounded-pill px-4">
                                <i class="fas fa-sign-in-alt me-2"></i> Login
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="mt-4 animate__animated animate__fadeInUp animate__delay-2s">
                            <?php if (hasRole('pet_owner')): ?>
                                <a class="btn btn-primary btn-lg rounded-pill px-4" href="pet_owner/dashboard.php">
                                    <i class="fas fa-tachometer-alt me-2"></i> Go to Dashboard
                                </a>
                            <?php elseif (hasRole('veterinarian')): ?>
                                <a class="btn btn-primary btn-lg rounded-pill px-4" href="veterinarian/dashboard.php">
                                    <i class="fas fa-tachometer-alt me-2"></i> Go to Dashboard
                                </a>
                            <?php elseif (hasRole('admin')): ?>
                                <a class="btn btn-primary btn-lg rounded-pill px-4" href="admin/dashboard.php">
                                    <i class="fas fa-tachometer-alt me-2"></i> Go to Dashboard
                                </a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            <div class="carousel-item" style="background-image: url('/vet_anywhere/assets/images/hero-2.jpg');">
                <div class="carousel-caption text-start">
                    <h1 class="display-4 fw-bold">For Pet Owners</h1>
                    <p class="lead">Keep track of vaccinations, medical history, and appointments</p>
                </div>
            </div>
            <div class="carousel-item" style="background-image: url('/vet_anywhere/assets/images/hero-3.jpg');">
                <div class="carousel-caption text-start">
                    <h1 class="display-4 fw-bold">For Veterinarians</h1>
                    <p class="lead">Streamline patient management and access complete medical records</p>
                </div>
            </div>
        </div>
        <button class="carousel-control-prev" type="button" data-bs-target="#heroCarousel" data-bs-slide="prev">
            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Previous</span>
        </button>
        <button class="carousel-control-next" type="button" data-bs-target="#heroCarousel" data-bs-slide="next">
            <span class="carousel-control-next-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Next</span>
        </button>
    </div>
</section>

<!-- Features Section -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="row justify-content-center mb-5">
            <div class="col-md-8 text-center">
                <h2 class="fw-bold mb-3">Why Choose Vet Anywhere?</h2>
                <p class="lead text-muted">A comprehensive solution for managing your pet's health needs</p>
            </div>
        </div>
        
        <div class="row g-4">
            <div class="col-md-4">
                <div class="card border-0 shadow-sm h-100 feature-card">
                    <div class="card-body text-center p-4">
                        <div class="feature-icon bg-primary text-white mb-4">
                            <i class="fas fa-calendar-check"></i>
                        </div>
                        <h4>Easy Scheduling</h4>
                        <p class="text-muted">Book appointments with your veterinarian at your convenience, anytime and anywhere.</p>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card border-0 shadow-sm h-100 feature-card">
                    <div class="card-body text-center p-4">
                        <div class="feature-icon bg-primary text-white mb-4">
                            <i class="fas fa-history"></i>
                        </div>
                        <h4>Complete History</h4>
                        <p class="text-muted">Access your pet's complete medical history including vaccinations, treatments, and medications.</p>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card border-0 shadow-sm h-100 feature-card">
                    <div class="card-body text-center p-4">
                        <div class="feature-icon bg-primary text-white mb-4">
                            <i class="fas fa-bell"></i>
                        </div>
                        <h4>Timely Reminders</h4>
                        <p class="text-muted">Never miss important vaccinations or medications with automated reminders.</p>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card border-0 shadow-sm h-100 feature-card">
                    <div class="card-body text-center p-4">
                        <div class="feature-icon bg-primary text-white mb-4">
                            <i class="fas fa-comments"></i>
                        </div>
                        <h4>Direct Communication</h4>
                        <p class="text-muted">Chat directly with your veterinarian for quick questions and follow-ups.</p>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card border-0 shadow-sm h-100 feature-card">
                    <div class="card-body text-center p-4">
                        <div class="feature-icon bg-primary text-white mb-4">
                            <i class="fas fa-file-medical"></i>
                        </div>
                        <h4>Digital Records</h4>
                        <p class="text-muted">All your pet's information is securely stored and accessible whenever needed.</p>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card border-0 shadow-sm h-100 feature-card">
                    <div class="card-body text-center p-4">
                        <div class="feature-icon bg-primary text-white mb-4">
                            <i class="fas fa-mobile-alt"></i>
                        </div>
                        <h4>Mobile Friendly</h4>
                        <p class="text-muted">Access the system from any device, anywhere, at any time.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- How It Works Section -->
<section class="py-5">
    <div class="container">
        <div class="row justify-content-center mb-5">
            <div class="col-md-8 text-center">
                <h2 class="fw-bold mb-3">How It Works</h2>
                <p class="lead text-muted">Simple steps to better pet healthcare management</p>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-10 mx-auto">
                <div class="timeline position-relative">
                    <!-- Timeline item 1 -->
                    <div class="timeline-item d-flex">
                        <div class="timeline-point"></div>
                        <div class="timeline-content shadow-sm p-4 rounded animate__animated animate__fadeInRight">
                            <div class="d-flex align-items-center mb-3">
                                <div class="timeline-icon bg-primary text-white me-3">
                                    <i class="fas fa-user-plus"></i>
                                </div>
                                <h4 class="mb-0">Create an Account</h4>
                            </div>
                            <p class="mb-0 text-muted">Register as a pet owner or veterinarian to get started with our platform.</p>
                        </div>
                    </div>
                    
                    <!-- Timeline item 2 -->
                    <div class="timeline-item d-flex">
                        <div class="timeline-point"></div>
                        <div class="timeline-content shadow-sm p-4 rounded animate__animated animate__fadeInLeft">
                            <div class="d-flex align-items-center mb-3">
                                <div class="timeline-icon bg-primary text-white me-3">
                                    <i class="fas fa-paw"></i>
                                </div>
                                <h4 class="mb-0">Add Your Pets</h4>
                            </div>
                            <p class="mb-0 text-muted">Enter your pets' details, upload photos, and start building their digital medical records.</p>
                        </div>
                    </div>
                    
                    <!-- Timeline item 3 -->
                    <div class="timeline-item d-flex">
                        <div class="timeline-point"></div>
                        <div class="timeline-content shadow-sm p-4 rounded animate__animated animate__fadeInRight">
                            <div class="d-flex align-items-center mb-3">
                                <div class="timeline-icon bg-primary text-white me-3">
                                    <i class="fas fa-calendar-alt"></i>
                                </div>
                                <h4 class="mb-0">Schedule Appointments</h4>
                            </div>
                            <p class="mb-0 text-muted">Book appointments with veterinarians based on availability and your schedule.</p>
                        </div>
                    </div>
                    
                    <!-- Timeline item 4 -->
                    <div class="timeline-item d-flex">
                        <div class="timeline-point"></div>
                        <div class="timeline-content shadow-sm p-4 rounded animate__animated animate__fadeInLeft">
                            <div class="d-flex align-items-center mb-3">
                                <div class="timeline-icon bg-primary text-white me-3">
                                    <i class="fas fa-stethoscope"></i>
                                </div>
                                <h4 class="mb-0">Receive Care</h4>
                            </div>
                            <p class="mb-0 text-muted">Visit your vet for the appointment, and they'll update your pet's medical records directly in the system.</p>
                        </div>
                    </div>
                    
                    <!-- Timeline item 5 -->
                    <div class="timeline-item d-flex">
                        <div class="timeline-point"></div>
                        <div class="timeline-content shadow-sm p-4 rounded animate__animated animate__fadeInRight">
                            <div class="d-flex align-items-center mb-3">
                                <div class="timeline-icon bg-primary text-white me-3">
                                    <i class="fas fa-chart-line"></i>
                                </div>
                                <h4 class="mb-0">Track Health Progress</h4>
                            </div>
                            <p class="mb-0 text-muted">Monitor your pet's health over time with comprehensive records and analytics.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Testimonials Section -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="row justify-content-center mb-5">
            <div class="col-md-8 text-center">
                <h2 class="fw-bold mb-3">What Our Users Say</h2>
                <p class="lead text-muted">Trusted by pet owners and veterinarians alike</p>
            </div>
        </div>
        
        <div class="row">
            <div class="col-12">
                <div id="testimonialCarousel" class="carousel slide" data-bs-ride="carousel">
                    <div class="carousel-inner">
                        <div class="carousel-item active">
                            <div class="row g-4">
                                <div class="col-md-6">
                                    <div class="card border-0 shadow-sm testimonial-card h-100">
                                        <div class="card-body p-4">
                                            <div class="d-flex align-items-center mb-3">
                                                <img src="/vet_anywhere/assets/images/testimonial-1.jpg" alt="User" class="rounded-circle me-3" width="60">
                                                <div>
                                                    <h5 class="mb-0">Sarah Johnson</h5>
                                                    <p class="text-muted mb-0 small">Pet Owner</p>
                                                </div>
                                            </div>
                                            <div class="testimonial-rating text-warning mb-3">
                                                <i class="fas fa-star"></i>
                                                <i class="fas fa-star"></i>
                                                <i class="fas fa-star"></i>
                                                <i class="fas fa-star"></i>
                                                <i class="fas fa-star"></i>
                                            </div>
                                            <p class="testimonial-text">"As a busy pet parent to three dogs, keeping track of vaccinations and vet visits was always a challenge. Vet Anywhere has simplified everything! I love getting reminders before appointments and having all records in one place."</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="card border-0 shadow-sm testimonial-card h-100">
                                        <div class="card-body p-4">
                                            <div class="d-flex align-items-center mb-3">
                                                <img src="/vet_anywhere/assets/images/testimonial-2.jpg" alt="User" class="rounded-circle me-3" width="60">
                                                <div>
                                                    <h5 class="mb-0">Dr. Michael Rivera</h5>
                                                    <p class="text-muted mb-0 small">Veterinarian</p>
                                                </div>
                                            </div>
                                            <div class="testimonial-rating text-warning mb-3">
                                                <i class="fas fa-star"></i>
                                                <i class="fas fa-star"></i>
                                                <i class="fas fa-star"></i>
                                                <i class="fas fa-star"></i>
                                                <i class="fas fa-star"></i>
                                            </div>
                                            <p class="testimonial-text">"This system has transformed my veterinary practice. Patient history is readily accessible, and the appointment system has reduced no-shows by 40%. My staff loves how user-friendly it is!"</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="carousel-item">
                            <div class="row g-4">
                                <div class="col-md-6">
                                    <div class="card border-0 shadow-sm testimonial-card h-100">
                                        <div class="card-body p-4">
                                            <div class="d-flex align-items-center mb-3">
                                                <img src="/vet_anywhere/assets/images/testimonial-3.jpg" alt="User" class="rounded-circle me-3" width="60">
                                                <div>
                                                    <h5 class="mb-0">James Wilson</h5>
                                                    <p class="text-muted mb-0 small">Pet Owner</p>
                                                </div>
                                            </div>
                                            <div class="testimonial-rating text-warning mb-3">
                                                <i class="fas fa-star"></i>
                                                <i class="fas fa-star"></i>
                                                <i class="fas fa-star"></i>
                                                <i class="fas fa-star"></i>
                                                <i class="fas fa-star"></i>
                                            </div>
                                            <p class="testimonial-text">"My cat has a chronic condition that requires regular monitoring. Vet Anywhere helps me track her symptoms, medication schedule, and treatment progress. It's been a game-changer for managing her health."</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="card border-0 shadow-sm testimonial-card h-100">
                                        <div class="card-body p-4">
                                            <div class="d-flex align-items-center mb-3">
                                                <img src="/vet_anywhere/assets/images/testimonial-4.jpg" alt="User" class="rounded-circle me-3" width="60">
                                                <div>
                                                    <h5 class="mb-0">Dr. Emily Chen</h5>
                                                    <p class="text-muted mb-0 small">Veterinary Clinic Owner</p>
                                                </div>
                                            </div>
                                            <div class="testimonial-rating text-warning mb-3">
                                                <i class="fas fa-star"></i>
                                                <i class="fas fa-star"></i>
                                                <i class="fas fa-star"></i>
                                                <i class="fas fa-star"></i>
                                                <i class="fas fa-star-half-alt"></i>
                                            </div>
                                            <p class="testimonial-text">"We implemented Vet Anywhere across our three clinic locations, and it has streamlined our operations significantly. The digital records are accessible from any location, which helps when patients visit different branches."</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <button class="carousel-control-prev" type="button" data-bs-target="#testimonialCarousel" data-bs-slide="prev">
                        <span class="carousel-control-prev-icon bg-primary rounded-circle" aria-hidden="true"></span>
                        <span class="visually-hidden">Previous</span>
                    </button>
                    <button class="carousel-control-next" type="button" data-bs-target="#testimonialCarousel" data-bs-slide="next">
                        <span class="carousel-control-next-icon bg-primary rounded-circle" aria-hidden="true"></span>
                        <span class="visually-hidden">Next</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="py-5 bg-primary text-white text-center">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <h2 class="fw-bold mb-4">Ready to Transform Your Pet's Healthcare?</h2>
                <p class="lead mb-4">Join thousands of pet owners and veterinarians already using Vet Anywhere</p>
                <a href="auth/register.php" class="btn btn-light btn-lg rounded-pill px-5">
                    <i class="fas fa-paw me-2"></i> Get Started for Free
                </a>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>