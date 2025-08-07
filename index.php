<?php
// Database configuration
$host = 'localhost';
$dbname = 'delphian_logic';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Create
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['create'])) {
    $section = $_POST['section'];
    $descriptions = [$_POST['description1'], $_POST['description2'], $_POST['description3']];
    $images = [];
    $svg_icon = '';
    for ($i = 1; $i <= 3; $i++) {
        if (isset($_FILES['image' . $i]) && $_FILES['image' . $i]['error'] == 0) {
            $target_dir = "uploads/";
            $target_file = $target_dir . basename($_FILES['image' . $i]["name"]);
            move_uploaded_file($_FILES['image' . $i]["tmp_name"], $target_file);
            $images[] = $target_file;
        } else {
            $images[] = '';
        }
    }
    if (isset($_FILES['svg_icon']) && $_FILES['svg_icon']['error'] == 0) {
        $target_dir = "uploads/";
        $svg_icon = $target_dir . basename($_FILES['svg_icon']['name']);
        move_uploaded_file($_FILES['svg_icon']['tmp_name'], $svg_icon);
    }
    $stmt = $pdo->prepare("INSERT INTO sections (section, description1, description2, description3, image1, image2, image3, svg_icon) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$section, $descriptions[0], $descriptions[1], $descriptions[2], $images[0], $images[1], $images[2], $svg_icon]);
    header("refresh: 0");
}

// Read
$sections = $pdo->query("SELECT * FROM sections")->fetchAll(PDO::FETCH_ASSOC);

// Update
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update'])) {
    $id = $_POST['id'];
    $section = $_POST['section'];
    $descriptions = [$_POST['description1'], $_POST['description2'], $_POST['description3']];
    $images = [
        isset($_POST['current_image1']) ? $_POST['current_image1'] : '',
        isset($_POST['current_image2']) ? $_POST['current_image2'] : '',
        isset($_POST['current_image3']) ? $_POST['current_image3'] : ''
    ];
    $current_svg = $_POST['current_svg_icon'];
    for ($i = 1; $i <= 4; $i++) {
        if (isset($_FILES['image' . $i]) && $_FILES['image' . $i]['error'] == 0) {
            $target_dir = "uploads/";
            $target_file = $target_dir . basename($_FILES['image' . $i]["name"]);
            move_uploaded_file($_FILES['image' . $i]["tmp_name"], $target_file);
            $images[$i - 1] = $target_file;
        }
    }
    $svg_icon = $current_svg;
    if (isset($_FILES['svg_icon']) && $_FILES['svg_icon']['error'] == 0) {
        $target_dir = "uploads/";
        $svg_icon = $target_dir . basename($_FILES['svg_icon']['name']);
        move_uploaded_file($_FILES['svg_icon']['tmp_name'], $svg_icon);
    }
    $stmt = $pdo->prepare("UPDATE sections SET section = ?, description1 = ?, description2 = ?, description3 = ?, image1 = ?, image2 = ?, image3 = ?, svg_icon = ? WHERE id = ?");
    $stmt->execute([$section, $descriptions[0], $descriptions[1], $descriptions[2], $images[0], $images[1], $images[2], $svg_icon, $id]);
    header("refresh: 0");
}

// Delete
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete'])) {
    $id = $_POST['id'];
    $stmt = $pdo->prepare("DELETE FROM sections WHERE id = ?");
    $stmt->execute([$id]);
    header("refresh: 0");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DelphianLogic in Action</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.8.1/slick.min.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.8.1/slick-theme.min.css" />
    <style>
        body {
            font-family: 'Open Sans', sans-serif;
            background-color: #2e3439;
            color: #696969;
            font-size: 16px;
            line-height: 1.625;
            padding: 20px;
        }
        h1 {
            font-family: 'Titillium Web', sans-serif;
            font-size: 40px;
            line-height: 1.2;
            font-weight: 700;
            color: #c4351e;
            margin-bottom: 0.75em;
            text-align: center;
        }
        h3 {
                font-size: 28px;
                line-height: 1.3;
            }
        .frame {
            border: 2px solid #64b4c8;
            border-radius: 5px;
            padding: 20px;
            background-color: #fff;
            margin-bottom: 20px;
        }
      
        .column, .merged-column {
            flex: 1;
            min-width: 0;
            display: flex;
            flex-direction: column;
        }
        .section-tab {
            background-color: #f8f9fa;
            color: #333;
            padding: 10px;
            margin-bottom: 5px;
            border-radius: 3px;
            cursor: pointer;
            display: flex;
            align-items: center;
            transition: background-color 0.3s;
            flex-grow: 0;
        }
        .section-tab:hover, .section-tab.active {
            background-color: #c4351e;
            color: #fff;
        }
        .section-tab img {
            margin-right: 10px;
            width: 20px;
        }
        .toggle-btn {
            width: 30px;
            height: 30px;
            background-color: #e34c4c;
            color: #fff;
            border: none;
            border-radius: 50%;
            font-size: 18px;
            cursor: pointer;
            margin-left: 10px;
            display: none;
        }
        .toggle-btn:hover {
            background-color: #c23535;
        }
        .slider {
            background-color: #64b4c8;
            color: #fff;
            padding: 15px;
            border-radius: 3px;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
            overflow: hidden;
            height: 350px;
        }
        
        
        }
        .slider-item {
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100%;
        }
        .slider-item p {
            margin: 0;
            font-size: 14px;
            line-height: 1.5;
        }
        .image-slider img {
            width: 100%;
            height: 100%;
            aspect-ratio: 1 / 1;
            object-fit: cover;
            display: block;
        }
        .merged-column {
            display: none;
            margin-top: 10px;
        }
        .merged-slider {
            position: relative;
            height: 100%;
            flex-grow: 1;
        }
        .merged-slider-item {
            position: relative;
            height: 200px;
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
        }
        .merged-slider-item .description {
            position: absolute;
            top: 50%;
            left: 50%;
             opacity: 0.9;
            transform: translate(-50%, -50%);
            color: #fff;
            text-align: center;
            padding: 15px;
            background-color: #64b4c8;
            border-radius: 5px;
            width: 100%;
            height: 100%;
            box-sizing: border-box;
        }
        .learn-more {
            color: #fff;
            text-decoration: underline;
            font-size: 12px;
            margin-top: 5px;
            display: block;
        }
        .btn {
            padding: 5px 10px;
            border-radius: 3px;
        }
        .btn-success { background-color: #56bd5b; color: #fff; }
        .btn-warning { background-color: #f7b422; color: #fff; }
        .btn-danger { background-color: #e34c4c; color: #fff; }
        .btn-info { background-color: #0099fa; color: #fff; }
        .form-label {
            font-weight: 600;
            color: #64b4c8;
        }
        .form-control {
            border-radius: 3px;
            padding: 5px;
        }
        .table-responsive {
            overflow-x: visible;
        }
        table {
            background-color: #f6f6f6;
            border-radius: 3px;
            width: 100%;
            min-width: 600px;
        }
        th, td {
            padding: 10px;
            border-bottom: 1px solid #d4d3d8;
            white-space: nowrap;
        }
        @media (max-width: 768px) {
            .three-column {
                flex-direction: column;
            }
            .column {
                width: 100%;
            }
            .section-tab {
                justify-content: space-between;
            }
            .toggle-btn {
                display: inline-block;
            }
            .slider-item, .merged-slider-item {
                height: 200px;
            }
            .three-column .column:nth-child(2),
            .three-column .column:nth-child(3) {
                display: none;
            }
            h1 {
                font-size: 30px;
            }
            
            h3 {
                font-size: 24px;
            }
            .merged-slider-item .description {
                font-size: 14px;
            }
          
             .table-responsive {
                overflow-x: auto;
            }
        }
        @media (min-width: 769px) {
            .three-column {
                flex-direction: row;
            }
            .merged-column {
                display: none;
            }
            .slider-item {
                height: 100%;
            }
            .table-responsive {
                overflow-x: auto;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>DelphianLogic in Action</h1>
        <?php if($sections){ ?>
        <div class="frame">
            <div class="row three-column">
                <div class="col-12 col-md-4 column">
                    <?php foreach ($sections as $index => $section): ?>
                        <div class="section-tab <?php echo $index === 0 ? 'active' : ''; ?>" data-index="<?php echo $index; ?>">
                            <?php if (!empty($section['svg_icon'])): ?>
                                <img src="<?php echo htmlspecialchars($section['svg_icon']); ?>" alt="<?php echo htmlspecialchars($section['section']); ?> Icon" width="20">
                            <?php endif; ?>
                            <?php echo htmlspecialchars($section['section']); ?>
                            <button class="toggle-btn" data-index="<?php echo $index; ?>">+</button>
                        </div>
                        <div class="merged-column" id="merged-column-<?php echo $section['id']; ?>">
                            <div class="merged-slider-container" id="merged-slider-<?php echo $section['id']; ?>">
                                <div class="merged-slider">
                                    <div class="merged-slider-item" style="background-image: url('<?php echo !empty($section['image1']) ? htmlspecialchars($section['image1']) : 'https://via.placeholder.com/150'; ?>');">
                                        <div class="description"><h3><?php echo htmlspecialchars($section['description1']); ?></h3><br><a href="#" class="learn-more">Learn More</a></div>
                                    </div>
                                    <div class="merged-slider-item" style="background-image: url('<?php echo !empty($section['image2']) ? htmlspecialchars($section['image2']) : 'https://via.placeholder.com/150'; ?>');">
                                        <div class="description"><h3><?php echo htmlspecialchars($section['description2']); ?></h3><br><a href="#" class="learn-more">Learn More</a></div>
                                    </div>
                                    <div class="merged-slider-item" style="background-image: url('<?php echo !empty($section['image3']) ? htmlspecialchars($section['image3']) : 'https://via.placeholder.com/150'; ?>');">
                                        <div class="description"><h3><?php echo htmlspecialchars($section['description3']); ?></h3><br><a href="#" class="learn-more">Learn More</a></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach;  ?>
                </div>
                <div class="col-12 col-md-4 column">
                    <?php foreach ($sections as $index => $section): ?>
                        <div class="slider-container <?php echo $index === 0 ? 'active' : ''; ?>" id="slider-<?php echo $section['id']; ?>" style="display: <?php echo $index === 0 ? 'block' : 'none'; ?>;">
                            <div class="slider">
                                <div class="slider-item">
                                    <h3><?php echo htmlspecialchars($section['description1']); ?></h3>
                                    <a href="#" class="learn-more">Learn More</a>
                                </div>
                                <div class="slider-item">
                                    <h3><?php echo htmlspecialchars($section['description2']); ?></h3>
                                    <a href="#" class="learn-more">Learn More</a>
                                </div>
                                <div class="slider-item">
                                    <h3><?php echo htmlspecialchars($section['description3']); ?></h3>
                                    <a href="#" class="learn-more">Learn More</a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div class="col-12 col-md-4 column">
                    <?php foreach ($sections as $index => $section): ?>
                        <div class="image-slider <?php echo $index === 0 ? 'active' : ''; ?>" id="image-slider-<?php echo $section['id']; ?>" style="display: <?php echo $index === 0 ? 'block' : 'none'; ?>;">
                            <div class="slider" style="background-color: #ffffffff; padding: 0px;">
                                <div><img src="<?php echo !empty($section['image1']) ? htmlspecialchars($section['image1']) : 'https://via.placeholder.com/150'; ?>" alt="Image 1"></div>
                                <div><img src="<?php echo !empty($section['image2']) ? htmlspecialchars($section['image2']) : 'https://via.placeholder.com/150'; ?>" alt="Image 2"></div>
                                <div><img src="<?php echo !empty($section['image3']) ? htmlspecialchars($section['image3']) : 'https://via.placeholder.com/150'; ?>" alt="Image 3"></div>
                            </div>
                        </div>
                    <?php endforeach;  ?>
                </div>
            </div>
        </div>
  <?php  } ?>
        <div class="mt-4">
            <h3>Manage Sections</h3>
            <?php if (isset($_POST['create']) || isset($_POST['update']) || isset($_POST['delete'])): ?>
                <div class="alert <?php echo $stmt->rowCount() > 0 ? 'alert-success' : 'alert-danger'; ?>" role="alert">
                    <?php echo $stmt->rowCount() > 0 ? 'Operation successful!' : 'Operation failed. Please try again.'; ?>
                </div>
            <?php endif; ?>
            <form method="post" enctype="multipart/form-data">
                <input type="hidden" name="id" id="edit-id">
                <input type="hidden" name="current_image1" id="current-image1">
                <input type="hidden" name="current_image2" id="current-image2">
                <input type="hidden" name="current_image3" id="current-image3">
                <input type="hidden" name="current_svg_icon" id="current-svg-icon">
                <div class="mb-3">
                    <label for="section" class="form-label">Section</label>
                    <input type="text" class="form-control" name="section" id="section" required value="<?php echo isset($_POST['section']) ? htmlspecialchars($_POST['section']) : ''; ?>">
                </div>
                <div class="mb-3">
                    <label for="description1" class="form-label">Description 1</label>
                    <textarea class="form-control" name="description1" id="description1" required><?php echo isset($_POST['description1']) ? htmlspecialchars($_POST['description1']) : ''; ?></textarea>
                </div>
                <div class="mb-3">
                    <label for="description2" class="form-label">Description 2</label>
                    <textarea class="form-control" name="description2" id="description2" required><?php echo isset($_POST['description2']) ? htmlspecialchars($_POST['description2']) : ''; ?></textarea>
                </div>
                <div class="mb-3">
                    <label for="description3" class="form-label">Description 3</label>
                    <textarea class="form-control" name="description3" id="description3" required><?php echo isset($_POST['description3']) ? htmlspecialchars($_POST['description3']) : ''; ?></textarea>
                </div>
                <div class="mb-3">
                    <label for="image1" class="form-label">Upload Image 1</label>
                    <input type="file" class="form-control" name="image1" id="image1" accept="image/jpeg,image/png,image/gif">
                </div>
                <div class="mb-3">
                    <label for="image2" class="form-label">Upload Image 2</label>
                    <input type="file" class="form-control" name="image2" id="image2" accept="image/jpeg,image/png,image/gif">
                </div>
                <div class="mb-3">
                    <label for="image3" class="form-label">Upload Image 3</label>
                    <input type="file" class="form-control" name="image3" id="image3" accept="image/jpeg,image/png,image/gif">
                </div>
                <div class="mb-3">
                    <label for="svg_icon" class="form-label">Upload Section SVG Icon</label>
                    <input type="file" class="form-control" name="svg_icon" id="svg_icon" accept="image/svg+xml">
                </div>
                <button type="submit" name="create" class="btn btn-success">Create</button>
                <button type="submit" name="update" class="btn btn-warning" id="update-btn" style="display: none;">Update</button>
                <button type="submit" name="delete" class="btn btn-danger" id="delete-btn" style="display: none;" onclick="return confirm('Are you sure you want to delete this section?');">Delete</button>
            </form>
             <?php if($sections){ ?>
            <div class="table-responsive">
                <table class="table mt-3">
                    <thead>
                        <tr>
                            <th>Section</th>
                            <th>Description 1</th>
                            <th>Description 2</th>
                            <th>Description 3</th>
                            <th>Image 1</th>
                            <th>Image 2</th>
                            <th>Image 3</th>
                            <th>SVG Icon</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($sections as $section): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($section['section']); ?></td>
                                <td><?php echo htmlspecialchars($section['description1']); ?></td>
                                <td><?php echo htmlspecialchars($section['description2']); ?></td>
                                <td><?php echo htmlspecialchars($section['description3']); ?></td>
                                <td><img src="<?php echo htmlspecialchars($section['image1']); ?>" style="width: 50px;"></td>
                                <td><img src="<?php echo htmlspecialchars($section['image2']); ?>" style="width: 50px;"></td>
                                <td><img src="<?php echo htmlspecialchars($section['image3']); ?>" style="width: 50px;"></td>
                                <td><?php echo !empty($section['svg_icon']) ? '<img src="' . htmlspecialchars($section['svg_icon']) . '" style="width: 50px;">' : ''; ?></td>
                                <td>
                                    <button class="btn btn-info edit-btn" data-id="<?php echo $section['id']; ?>" 
                                            data-section="<?php echo htmlspecialchars($section['section']); ?>" 
                                            data-description1="<?php echo htmlspecialchars($section['description1']); ?>" 
                                            data-description2="<?php echo htmlspecialchars($section['description2']); ?>" 
                                            data-description3="<?php echo htmlspecialchars($section['description3']); ?>" 
                                            data-image1="<?php echo htmlspecialchars($section['image1']); ?>" 
                                            data-image2="<?php echo htmlspecialchars($section['image2']); ?>" 
                                            data-image3="<?php echo htmlspecialchars($section['image3']); ?>" 
                                            data-svg-icon="<?php echo htmlspecialchars($section['svg_icon']); ?>">Edit</button>
                                    <button class="btn btn-danger delete-btn" data-id="<?php echo $section['id']; ?>" onclick="return confirm('Are you sure you want to delete this section?');">Delete</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
             <?php }?>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.8.1/slick.min.js"></script>
    <script>
        $(document).ready(function(){
            function initializeDesktopSliders() {
                $('.slider-container').each(function(index) {
                    var $container = $(this);
                    var $descSlider = $container.find('.slider');
                    var $imgSlider = $('.image-slider').eq(index).find('.slider');

                    if ($descSlider.hasClass('slick-initialized')) $descSlider.slick('unslick');
                    if ($imgSlider.hasClass('slick-initialized')) $imgSlider.slick('unslick');

                    $descSlider.slick({
                        slidesToShow: 1,
                        slidesToScroll: 1,
                        autoplay: true,
                        autoplaySpeed: 3000,
                        arrows: false,
                        fade: false,
                        asNavFor: $imgSlider
                    });

                    $imgSlider.slick({
                        slidesToShow: 1,
                        slidesToScroll: 1,
                        autoplay: true,
                        autoplaySpeed: 3000,
                        arrows: false,
                        fade: true,
                        asNavFor: $descSlider
                    }).on('init', function(event, slick){
                        $(this).find('img').each(function(){
                            if (!this.complete || typeof this.naturalWidth === "undefined" || this.naturalWidth === 0) {
                                $(this).attr('src', 'https://via.placeholder.com/150');
                            }
                        });
                    });
                });
            }

            function initializeMergedSliders() {
                $('.merged-slider').each(function() {
                    var $mergedSlider = $(this);
                    if ($mergedSlider.hasClass('slick-initialized')) $mergedSlider.slick('unslick');
                    $mergedSlider.slick({
                        slidesToShow: 1,
                        slidesToScroll: 1,
                        autoplay: true,
                        autoplaySpeed: 3000,
                        arrows: false,
                        fade: false
                    });
                });
            }

            function initializeSliders() {
                if (window.innerWidth <= 768) {
                    initializeMergedSliders();
                    if ($('.section-tab').length > 0 && !$('.section-tab').eq(0).hasClass('active')) {
                        $('.section-tab').eq(0).addClass('active');
                        $('#merged-column-<?php echo $sections[0]['id'] ?? ''; ?>').show();
                        $('.section-tab').eq(0).find('.toggle-btn').text('-');
                    }
                } else {
                    initializeDesktopSliders();
                }
            }
            initializeSliders();

            // Toggle section tab and merged column
            $('.column').eq(0).on('click', '.section-tab', function() {
                var index = $(this).data('index');
                var $mergedColumn = $(this).next('.merged-column');
                var $toggleBtn = $(this).find('.toggle-btn');

                if (window.innerWidth <= 768) {
                    if ($mergedColumn.is(':visible')) {
                        $mergedColumn.hide();
                        $toggleBtn.text('+');
                        $(this).removeClass('active');
                    } else {
                        $('.section-tab').removeClass('active');
                        $('.merged-column').hide();
                        $(this).addClass('active');
                        $mergedColumn.show();
                        $toggleBtn.text('-');
                        $mergedColumn.find('.merged-slider').slick('slickGoTo', 0);
                        $('.section-tab').not(this).find('.toggle-btn').text('+');
                    }
                } else {
                    $('.section-tab').removeClass('active');
                    $('.slider-container, .image-slider').hide();
                    $(this).addClass('active');
                    $('.slider-container').eq(index).show();
                    $('.image-slider').eq(index).show();
                    $('.slider-container').eq(index).find('.slider').slick('slickGoTo', 0);
                    $('.image-slider').eq(index).find('.slider').slick('slickGoTo', 0);
                }
            });

            // Reinitialize sliders on window resize without resetting active state
            $(window).resize(function() {
                var activeIndex = $('.section-tab.active').data('index') || 0;
                initializeSliders();
                if (window.innerWidth <= 768) {
                    $('.merged-column').hide();
                    $('.merged-column').eq(activeIndex).show();
                    $('.section-tab').eq(activeIndex).addClass('active').find('.toggle-btn').text('-');
                    $('.section-tab').not('.section-tab').eq(activeIndex).find('.toggle-btn').text('+');
                } else {
                    $('.slider-container, .image-slider').hide();
                    $('.slider-container').eq(activeIndex).show();
                    $('.image-slider').eq(activeIndex).show();
                    $('.slider-container').eq(activeIndex).find('.slider').slick('slickGoTo', 0);
                    $('.image-slider').eq(activeIndex).find('.slider').slick('slickGoTo', 0);
                }
            });

            // Reinitialize sliders after DOM updates without resetting active state
            $('form').on('submit', function(){
                setTimeout(function(){
                    var activeIndex = $('.section-tab.active').data('index') || 0;
                    initializeSliders();
                    if (window.innerWidth <= 768) {
                        $('.merged-column').hide();
                        $('.merged-column').eq(activeIndex).show();
                        $('.section-tab').eq(activeIndex).addClass('active').find('.toggle-btn').text('-');
                        $('.section-tab').not('.section-tab').eq(activeIndex).find('.toggle-btn').text('+');
                    } else {
                        $('.slider-container').eq(activeIndex).show();
                        $('.image-slider').eq(activeIndex).show();
                        $('.slider-container').eq(activeIndex).find('.slider').slick('slickGoTo', 0);
                        $('.image-slider').eq(activeIndex).find('.slider').slick('slickGoTo', 0);
                    }
                }, 100);
            });

            $('.edit-btn').click(function(){
                $('#edit-id').val($(this).data('id'));
                $('#section').val($(this).data('section'));
                $('#description1').val($(this).data('description1'));
                $('#description2').val($(this).data('description2'));
                $('#description3').val($(this).data('description3'));
                $('#current-image1').val($(this).data('image1'));
                $('#current-image2').val($(this).data('image2'));
                $('#current-image3').val($(this).data('image3'));
                $('#current-svg-icon').val($(this).data('svg-icon'));
                $('#update-btn').show();
                $('#delete-btn').show();
                $('#create').hide();
            });

            $('.delete-btn').click(function(){
                
                $('#edit-id').val($(this).data('id'));
                  $('form').append('<input type="hidden" name="delete" value="1">').submit();
            });
        });
    </script>
</body>
</html>