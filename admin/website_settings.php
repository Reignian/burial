<?php
    require_once('nav/navigation.php');
    require_once('settings/website.class.php');

    $websiteSettings = new WebsiteSettings();

    // Get all website content
    $pubmat1 = $websiteSettings->getPubmat1();
    $pubmat2 = $websiteSettings->getPubmat2();
    $about_main = $websiteSettings->getAboutMain();
    $about = $websiteSettings->getAbout();
    $about_2 = $websiteSettings->getAbout2();
    $about_team = $websiteSettings->getAboutTeam();
    $contact = $websiteSettings->getContact();
?>

<style>
    .display-5 {
    font-size: 2.5rem !important;
    margin-bottom: 0.5rem !important;
    letter-spacing: 1px !important;
    color: #00838f !important;
}
</style>

<div class="container-fluid px-4 py-4">
    <div class="text-center mb-4">
        <h1 class="display-5 fw-bold text-uppercase" style="color: #006064;">Website Settings</h1>

        <div class="border-bottom border-2 w-25 mx-auto" style="border-color: #006064 !important;"></div>
    </div>

    <!-- Navigation Tabs -->
    <ul class="nav nav-tabs mb-4" id="settingsTabs" role="tablist">
        <li class="nav-item">
            <a class="nav-link active" id="hero-tab" data-bs-toggle="tab" href="#hero" role="tab">Landing Page</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" id="about-tab" data-bs-toggle="tab" href="#about" role="tab">About Page</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" id="contact-tab" data-bs-toggle="tab" href="#contact" role="tab">Contact Info</a>
        </li>
    </ul>

    <!-- Tab Content -->
    <div class="tab-content" id="settingsTabContent">
        <!-- Hero Section Tab -->
        <div class="tab-pane fade show active" id="hero" role="tabpanel">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h4 class="mt-4 text-uppercase" style="color: #006064;">Pubmat Carousel</h4>
                    
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Image</th>
                                <th>Heading</th>
                                <th>Text</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($pubmat1 as $item): ?>
                                <tr>
                                    <td><img src="../<?=$item['image']?>" alt="" style="width: 100px; height: 100px; object-fit: cover;"></td>
                                    <td><?=$item['heading']?></td>
                                    <td><?=$item['text']?></td>
                                    <td>
                                        <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#editModal"
                                            data-id="<?=$item['id']?>"
                                            data-heading="<?=htmlspecialchars($item['heading'], ENT_QUOTES)?>"
                                            data-text="<?=htmlspecialchars($item['text'], ENT_QUOTES)?>"
                                            data-image="<?=$item['image']?>">
                                            Edit
                                        </button>
                                        <form method="POST" action="settings/handle_website_settings.php" style="display: inline;">
                                            <input type="hidden" name="id" value="<?=$item['id']?>">
                                            <button type="submit" name="delete_pubmat1" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this item?')">
                                                Delete
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>

                    <hr class="my-4">

                    <form method="POST" action="settings/handle_website_settings.php" enctype="multipart/form-data" class="mb-4">
                        <div class="mb-3">
                            <label class="form-label">Heading</label>
                            <input type="text" class="form-control" name="heading" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Text</label>
                            <textarea class="form-control" name="text" required rows="3"></textarea>
                        </div>
        
                        <div class="mb-3">
                            <label class="form-label">Image</label>
                            <input type="file" class="form-control" name="image" accept="image/*" required>
                        </div>
                        
                        <button type="submit" name="add_pubmat1" class="btn text-white" style="background-color: #006064;">Add New Carousel Item</button>
                    </form>
                    <hr class="my-4">

                    <br>

                    <h4 class="mt-4 text-uppercase" style="color: #006064;">Displayed Sample Lots</h4>

                    <table class="table">
                        <thead>
                            <tr>
                                <th>Image</th>
                                <th>Heading</th>
                                <th>Text</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($pubmat2 as $item): ?>
                                <tr>
                                    <td><img src="../<?=$item['image']?>" alt="" style="width: 100px; height: 100px; object-fit: cover;"></td>
                                    <td><?=$item['heading']?></td>
                                    <td><?=$item['text']?></td>
                                    <td>
                                        <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#editModal2"
                                            data-id="<?=$item['id']?>"
                                            data-heading="<?=htmlspecialchars($item['heading'], ENT_QUOTES)?>"
                                            data-text="<?=htmlspecialchars($item['text'], ENT_QUOTES)?>"
                                            data-image="<?=$item['image']?>">
                                            Edit
                                        </button>
                                        <form method="POST" action="settings/handle_website_settings.php" style="display: inline;">
                                            <input type="hidden" name="id" value="<?=$item['id']?>">
                                            <button type="submit" name="delete_pubmat2" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this item?')">
                                                Delete
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <hr class="my-4">

                    <form method="POST" action="settings/handle_website_settings.php" enctype="multipart/form-data" class="mb-4">
                        <div class="mb-3">
                            <label class="form-label">Heading</label>
                            <input type="text" class="form-control" name="heading" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Text</label>
                            <textarea class="form-control" name="text" required rows="3"></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Image</label>
                            <input type="file" class="form-control" name="image" accept="image/*" required>
                        </div>
                        
                        <button type="submit" name="add_pubmat2" class="btn text-white" style="background-color: #006064;">Add New Display</button>
                    </form>
                </div>
            </div>
        </div>


        <!-- About Page Tab -->
        <div class="tab-pane fade" id="about" role="tabpanel">
            <div class="card shadow-sm">
                <div class="card-body">
                    
                    <h3 class="card-title text-uppercase mb-4" style="color: #006064;">Section 1</h3>

                    <form method="POST" action="settings/handle_website_settings.php" class="mb-4">
                        
                        <div class="mb-3">
                            <label class="form-label">Title</label>
                            <input type="text" class="form-control" name="section_title1" value="<?= $about[0]['section_title'] ?>">
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Sub Title</label>
                            <input type="text" class="form-control" name="sub_title1" value="<?= $about[0]['sub_title'] ?>">
                        </div>
                        
                        <button type="submit" name="update_about1" class="btn btn-primary" style="background-color: #006064; border: none;">Update</button>
                    </form>

                    <!-- About main Content -->
                    <h3 class="card-title text-uppercase mb-4" style="color: #006064;">Content</h3>
                    <form method="POST" action="settings/handle_website_settings.php" enctype="multipart/form-data" class="mb-4">

                        <div class="mb-3">
                            <label class="form-label">Current Image</label>
                            <img src="../<?=$about_main[0]['image']?>" style="width: 100px; height: 100px;" alt="">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">New Image</label>
                            <input type="file" class="form-control" name="image" accept="image/*">
                        </div>
                        
                        <div class="mb-3">
                            <label for="text" class="form-label">Text Content</label>
                            <textarea class="form-control" id="text" name="text" rows="6"><?= htmlspecialchars($about_main[0]['text']) ?></textarea>
                        </div>
                        
                        <button type="submit" name="update_main" class="btn btn-primary" style="background-color: #006064; border: none;">Update</button>
                    </form>


                    <hr class="my-4">

                    <br>




                    <h3 class="card-title text-uppercase mb-4" style="color: #006064;">Section 2</h3>

                    <form method="POST" action="settings/handle_website_settings.php" class="mb-4">
                        
                        <div class="mb-3">
                            <label class="form-label">Section Title</label>
                            <input type="text" class="form-control" name="section_title2" value="<?= $about[1]['section_title'] ?>">
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Sub Title</label>
                            <input type="text" class="form-control" name="sub_title2" value="<?= $about[1]['sub_title'] ?>">
                        </div>
                        
                        <button type="submit" name="update_about2" class="btn btn-primary" style="background-color: #006064; border: none;">Update</button>
                    </form>


                    <!-- Cards Content -->
                    <h3 class="card-title text-uppercase mb-4" style="color: #006064;">Card Content 1</h3>
                    <form method="POST" action="settings/handle_website_settings.php" class="mb-4">
                        
                        <div class="mb-3">
                            <label class="form-label">Title</label>
                            <input type="text" class="form-control" name="card_title1" value="<?= $about_2[0]['card_title'] ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Text</label>
                            <textarea class="form-control" name="card_text1" required rows="3"><?= $about_2[0]['card_text'] ?></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Icon (Font Awesome Class)</label>
                            <input type="text" class="form-control" name="card_icon1" value="<?= $about_2[0]['card_icon'] ?>" required>
                        </div>
                        
                        <button type="submit" name="update_card1" class="btn btn-primary" style="background-color: #006064; border: none;">Update</button>
                    </form>

        
                    <!-- Cards Content -->
                    <h3 class="card-title text-uppercase mb-4" style="color: #006064;">Card Content 2</h3>
                    <form method="POST" action="settings/handle_website_settings.php" class="mb-4">
                        
                        <div class="mb-3">
                            <label class="form-label">Title</label>
                            <input type="text" class="form-control" name="card_title2" value="<?= $about_2[1]['card_title'] ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Text</label>
                            <textarea class="form-control" name="card_text2" required rows="3"><?= $about_2[1]['card_text'] ?></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Icon (Font Awesome Class)</label>
                            <input type="text" class="form-control" name="card_icon2" value="<?= $about_2[1]['card_icon'] ?>" required>
                        </div>
                        
                        <button type="submit" name="update_card2" class="btn btn-primary" style="background-color: #006064; border: none;">Update</button>
                    </form>


                    <!-- Cards Content -->
                    <h3 class="card-title text-uppercase mb-4" style="color: #006064;">Card Content 3</h3>
                    <form method="POST" action="settings/handle_website_settings.php" class="mb-4">
                        
                        <div class="mb-3">
                            <label class="form-label">Title</label>
                            <input type="text" class="form-control" name="card_title3" value="<?= $about_2[2]['card_title'] ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Text</label>
                            <textarea class="form-control" name="card_text3" required rows="3"><?= $about_2[2]['card_text'] ?></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Icon (Font Awesome Class)</label>
                            <input type="text" class="form-control" name="card_icon3" value="<?= $about_2[2]['card_icon'] ?>" required>
                        </div>
                        
                        <button type="submit" name="update_card3" class="btn btn-primary" style="background-color: #006064; border: none;">Update</button>
                    </form>

                    <hr class="my-4">
                    <br>


                    <h3 class="card-title text-uppercase mb-4" style="color: #006064;">Section 3</h3>
                    <form method="POST" action="settings/handle_website_settings.php" class="mb-4">
                        
                        <div class="mb-3">
                            <label class="form-label">Section Title</label>
                            <input type="text" class="form-control" name="section_title3" value="<?= $about[2]['section_title'] ?>">
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Sub Title</label>
                            <input type="text" class="form-control" name="sub_title3" value="<?= $about[2]['sub_title'] ?>">
                        </div>
                        
                        <button type="submit" name="update_about3" class="btn btn-primary" style="background-color: #006064; border: none;">Update</button>
                    </form>


                    <!-- Cards Content -->
                    <h3 class="card-title text-uppercase mb-4" style="color: #006064;">Card Content 1</h3>
                    <form method="POST" action="settings/handle_website_settings.php" class="mb-4">
                        
                        <div class="mb-3">
                            <label class="form-label">Title</label>
                            <input type="text" class="form-control" name="card_title4" value="<?= $about_2[3]['card_title'] ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Text</label>
                            <textarea class="form-control" name="card_text4" required rows="3"><?= $about_2[3]['card_text'] ?></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Icon (Font Awesome Class)</label>
                            <input type="text" class="form-control" name="card_icon4" value="<?= $about_2[3]['card_icon'] ?>" required>
                        </div>
                        
                        <button type="submit" name="update_card4" class="btn btn-primary" style="background-color: #006064; border: none;">Update</button>
                    </form>


                    <!-- Cards Content -->
                    <h3 class="card-title text-uppercase mb-4" style="color: #006064;">Card Content 2</h3>
                    <form method="POST" action="settings/handle_website_settings.php" class="mb-4">
                        
                        <div class="mb-3">
                            <label class="form-label">Title</label>
                            <input type="text" class="form-control" name="card_title5" value="<?= $about_2[4]['card_title'] ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Text</label>
                            <textarea class="form-control" name="card_text5" required rows="3"><?= $about_2[4]['card_text'] ?></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Icon (Font Awesome Class)</label>
                            <input type="text" class="form-control" name="card_icon5" value="<?= $about_2[4]['card_icon'] ?>" required>
                        </div>
                        
                        <button type="submit" name="update_card5" class="btn btn-primary" style="background-color: #006064; border: none;">Update</button>
                    </form>

                    <hr class="my-4">
                    <br>



                    <h3 class="card-title text-uppercase mb-4" style="color: #006064;">Section 4</h3>

                    <form method="POST" action="settings/handle_website_settings.php" class="mb-4">
                        
                        <div class="mb-3">
                            <label class="form-label">Section Title</label>
                            <input type="text" class="form-control" name="section_title4" value="<?= $about[3]['section_title'] ?>">
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Sub Title</label>
                            <input type="text" class="form-control" name="sub_title4" value="<?= $about[3]['sub_title'] ?>">
                        </div>
                        
                        <button type="submit" name="update_about4" class="btn btn-primary" style="background-color: #006064; border: none;">Update</button>
                    </form>

                    <h3 class="card-title text-uppercase mb-4" style="color: #006064;">Team Members</h3>

                    <table class="table">
                        <thead>
                            <tr>
                                <th>Image</th>
                                <th>Name</th>
                                <th>Position</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($about_team as $item): ?>
                                <tr>
                                    <td><img src="../<?=$item['image']?>" alt="" style="width: 100px; height: 100px; object-fit: cover;"></td>
                                    <td><?=$item['name']?></td>
                                    <td><?=$item['position']?></td>
                                    <td>
                                        <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#editTeamModal"
                                            data-id="<?=$item['id']?>"
                                            data-name="<?=htmlspecialchars($item['name'], ENT_QUOTES)?>"
                                            data-position="<?=htmlspecialchars($item['position'], ENT_QUOTES)?>"
                                            data-image="<?=$item['image']?>">
                                            Edit
                                        </button>
                                        <form method="POST" action="settings/handle_website_settings.php" style="display: inline;">
                                            <input type="hidden" name="id" value="<?=$item['id']?>">
                                            <button type="submit" name="delete_team_member" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this team member?')">
                                                Delete
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>

                    <hr class="my-4">

                    <!-- Add New Team Member Form -->
                    <form method="POST" action="settings/handle_website_settings.php" enctype="multipart/form-data" class="mb-4">
                        <div class="mb-3">
                            <label class="form-label">Name</label>
                            <input type="text" class="form-control" name="name" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Position</label>
                            <input type="text" class="form-control" name="position" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Image</label>
                            <input type="file" class="form-control" name="image" accept="image/*" required>
                        </div>
                        
                        <button type="submit" name="add_team_member" class="btn text-white" style="background-color: #006064;">Add New Team Member</button>
                    </form>

                </div>
            </div>
        </div>

        <!-- Contact Info Tab -->
        <div class="tab-pane fade" id="contact" role="tabpanel">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h3 class="card-title text-uppercase mb-4" style="color: #006064;">Contact Information</h3>
                    <form method="POST" action="settings/handle_website_settings.php">
                        
                        <div class="mb-3">
                            <label class="form-label">Phone</label>
                            <input type="text" class="form-control" name="phone" value="<?= $contact['phone'] ?>">
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" name="email" value="<?= $contact['email'] ?>">
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Address</label>
                            <textarea class="form-control" name="address" rows="3"><?= $contact['address'] ?></textarea>
                        </div>

                        <button type="submit" name="update_contact" class="btn text-white" style="background-color: #006064;">Update Contact Information</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Edit Modal (outside all content) -->
<div class="modal fade" id="editModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Carousel Item</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="settings/handle_website_settings.php" enctype="multipart/form-data">
                <div class="modal-body">
                    <input type="hidden" name="id" id="edit_id">
                    <input type="hidden" name="current_image" id="edit_current_image">
                    
                    <div class="mb-3">
                        <label class="form-label">Heading</label>
                        <input type="text" class="form-control" name="heading" id="edit_heading" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Text</label>
                        <textarea class="form-control" name="text" id="edit_text" required rows="3"></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Current Image</label>
                        <img id="edit_image_preview" class="img-thumbnail d-block" style="max-width: 200px">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">New Image (optional)</label>
                        <input type="file" class="form-control" name="image" accept="image/*">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" name="update_pubmat1" class="btn text-white" style="background-color: #006064;">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Modal for Sample Lots -->
<div class="modal fade" id="editModal2" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Sample Lot</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="settings/handle_website_settings.php" enctype="multipart/form-data">
                <div class="modal-body">
                    <input type="hidden" name="id" id="edit_id2">
                    <input type="hidden" name="current_image" id="edit_current_image2">
                    
                    <div class="mb-3">
                        <label class="form-label">Heading</label>
                        <input type="text" class="form-control" name="heading" id="edit_heading2" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Text</label>
                        <textarea class="form-control" name="text" id="edit_text2" required rows="3"></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Current Image</label>
                        <img id="edit_image_preview2" class="img-thumbnail d-block" style="max-width: 200px">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">New Image (optional)</label>
                        <input type="file" class="form-control" name="image" accept="image/*">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" name="update_pubmat2" class="btn text-white" style="background-color: #006064;">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Team Member Modal -->
<div class="modal fade" id="editTeamModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Team Member</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="settings/handle_website_settings.php" enctype="multipart/form-data">
                <div class="modal-body">
                    <input type="hidden" name="id" id="edit_team_id">
                    
                    <div class="mb-3">
                        <label class="form-label">Name</label>
                        <input type="text" class="form-control" name="name" id="edit_team_name" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Position</label>
                        <input type="text" class="form-control" name="position" id="edit_team_position" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Current Image</label>
                        <img id="edit_team_current_image" class="img-thumbnail d-block" style="max-width: 200px">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">New Image (optional)</label>
                        <input type="file" class="form-control" name="image" accept="image/*">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" name="update_team_member" class="btn text-white" style="background-color: #006064;">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal initialization scripts -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Hero Carousel Modal
    const editModal = document.getElementById('editModal');
    editModal.addEventListener('show.bs.modal', function (event) {
        const button = event.relatedTarget;
        const id = button.getAttribute('data-id');
        const heading = button.getAttribute('data-heading');
        const text = button.getAttribute('data-text');
        const image = button.getAttribute('data-image');
        
        document.getElementById('edit_id').value = id;
        document.getElementById('edit_heading').value = heading;
        document.getElementById('edit_text').value = text;
        document.getElementById('edit_current_image').value = image;
        document.getElementById('edit_image_preview').src = '../' + image;
    });

    // Sample Lots Modal
    const editModal2 = document.getElementById('editModal2');
    editModal2.addEventListener('show.bs.modal', function (event) {
        const button = event.relatedTarget;
        const id = button.getAttribute('data-id');
        const heading = button.getAttribute('data-heading');
        const text = button.getAttribute('data-text');
        const image = button.getAttribute('data-image');
        
        document.getElementById('edit_id2').value = id;
        document.getElementById('edit_heading2').value = heading;
        document.getElementById('edit_text2').value = text;
        document.getElementById('edit_current_image2').value = image;
        document.getElementById('edit_image_preview2').src = '../' + image;
    });

    // Team Members Modal
    const editTeamModal = document.getElementById('editTeamModal');
    editTeamModal.addEventListener('show.bs.modal', function (event) {
        const button = event.relatedTarget;
        const id = button.getAttribute('data-id');
        const name = button.getAttribute('data-name');
        const position = button.getAttribute('data-position');
        const image = button.getAttribute('data-image');
        
        document.getElementById('edit_team_id').value = id;
        document.getElementById('edit_team_name').value = name;
        document.getElementById('edit_team_position').value = position;
        document.getElementById('edit_team_current_image').src = '../' + image;
    });
});
</script>