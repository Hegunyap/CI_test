var sass = require('node-sass');

module.exports = function(grunt) {
    // project configuration
    grunt.initConfig({
        // metadata
        pkg: grunt.file.readJSON('package.json'),
        logo: '*            .--://////::-.`                                                                                                                            \n' +
            '*        `-/++++++++++++++++/:.                                                                                                                         \n' +
            '*      ./++++++++++++++++++++++/-                                                                                                                       \n' +
            '*    `/+++++++++++++/++++++++++++/.                                                 .:                                                                  \n' +
            '*   .+++++/.++++++/.`/++++++:.+++++:         .+`       /+`       /-                 -/                    `+`                                           \n' +
            '*  -++++++/ `/+++/`   :++++- .++++++:         /-      -:-:      ./                  -/                    `+`                                           \n' +
            '* `+++++++/  `/+++:  .+++/.  .+++++++-        -/      /``+`     /-      `----.`     -/ `.----`            `+`    `.  .---.   `.---.       .  `----.     \n' +
            '* :+++++++/    :++:  .++/`   .+++++++/         /.    -:  ::    `+`    ./-`  `./-    -/:-.`  `:/.          `+`    ./-:.` `-/.:-`  `:/`     +::.`  `-/-   \n' +
            '* /+++++++/  `::++:  .++::.  .++++++++`        -/   `+`  `+.   ::    ./`       /.   -/        -+`         `+`    .+`      :/       /-     +.       `/-  \n' +
            '* /+++++++/  `++++:  .++++-  .++++++++`        `+.  ::    -/  `+`    /-````````::   -/         +.         `+`    .+       -:       ::     +`        :/  \n' +
            '* :+++++++/  `++++:  .++++-  .+++++++/          :: `+`     /. -:     +-``````````   -/         +-         `+`    .+       -:       ::     +`        :/  \n' +
            '* `+++++++/  `++++:  .++++-  .+++++++-          `+`-:      -/ /.     :/             -/        -+`         `+`    .+       -:       ::     +`       `/-  \n' +
            '*  -++++++/   `````  .+:```  .++++++:            ::/`       /:/       :/`      `    -/.     `-/.          `+`    .+       -:       ::     +-`     ./-   \n' +
            '*   .+++++/````````  .+-`````-+++++:             `:-        .:.        `-::--::.    .-.-:::::.            `:`    `:       .-       --     +.-:::::-`    \n' +
            '*    `/+++++++++++:  .+++++++++++/.                                                                                                       +`            \n' +
            '*      ./+++++++++:  .+++++++++/-                                                                                                         +`            \n' +
            '*        `-/++++++:  .++++++/-.                                                                                                           /`            \n' +
            '*            .--:/-  ./::-.`',
        message: '* You appear to be one of those insatiably curious engineers who always wants to know what\'s under the covers\n' +
            '* and make it better. We are a like-minded group of engineers and designers implementing this solution. We\'ve\n' +
            '* got hard problems to solve and some amazing technology to do so: come join us and be part of it all!\n*\n' +
            '* Now hiring @ https://www.webimp.com.sg/career/',
        banner: '/*\n' +
            '* <%= pkg.title || pkg.name %> - v<%= pkg.version %> - <%= grunt.template.today("yyyy-mm-dd") %>\n' +
            '<%= pkg.homepage ? "* " + pkg.homepage + "\\n" : "" %>' +
            '* Copyright (c) <%= grunt.template.today("yyyy") %> <%= pkg.author %>\n*\n<%= logo %>\n*\n<%= message %>\n*/\n\n',
        output: {
            global: {
                js: 'assets/js/<%= pkg.name %>.min.js',
                css: 'assets/css/<%= pkg.name %>.min.css',
            },
            fontawesome: {
                css: 'assets/css/fontawesome.min.css',
            },
            vendor: {
                css: 'assets/css/vendor.min.css',
            },
            forms: {
                base: 'assets/js/forms/base.min.js',
                superAdmin: 'assets/js/forms/superadmin.min.js',
            },
            highcharts: {
                base: 'assets/js/highcharts/base.js',
                superAdmin: 'assets/js/highcharts/superadmin.min.js',
            },
            datatables: {
                base: 'assets/js/datatables.min.js',
                superAdmin: 'assets/js/datatables/superadmin.min.js',
            },
        },
        // task configuration
        header: {
            options: {
                text: '<%= banner %>',
            },
            global: {
                files: {
                    '<%= output.global.css %>': '<%= output.global.css %>',
                    '<%= output.global.js %>': '<%= output.global.js %>',
                },
            },
            vendor: {
                files: {
                    '<%= output.vendor.css %>': '<%= output.vendor.css %>',
                },
            },
            forms: {
                files: {
                    '<%= output.forms.base %>': '<%= output.forms.base %>',
                    '<%= output.forms.superAdmin %>': '<%= output.forms.superAdmin %>',
                },
            },
            highcharts: {
                files: {
                    '<%= output.highcharts.superAdmin %>': '<%= output.highcharts.superAdmin %>',
                },
            },
            datatables: {
                files: {
                    '<%= output.datatables.base %>': '<%= output.datatables.base %>',
                    '<%= output.datatables.superAdmin %>': '<%= output.datatables.superAdmin %>',
                },
            },
            fontawesome: {
                files: {
                    '<%= output.fontawesome.css %>': '<%= output.fontawesome.css %>',
                },
            },
        },
        concat: {
            options: {
                stripBanners: true
            },
            global: {
                dest: '.tmp/js/<%= pkg.name %>.js',
                src: [
                    // third parties
                    'node_modules/bootstrap-sass/assets/javascripts/bootstrap.js',
                    'node_modules/metismenu/dist/metisMenu.js',
                    'node_modules/jquery-slimscroll/jquery.slimscroll.js',
                    'node_modules/pace-progress/pace.js',
                    'node_modules/moment/moment.js',
                    'node_modules/select2/dist/js/select2.js',
                    // custom functions
                    'assets/src/js/functions.js',
                    // inspinia
                    'assets/src/js/app.js',
                    // global scripts
                    'assets/src/js/global.js',
                ],
            },
            datatables: {
                dest: '.tmp/js/datatables.js',
                src: [
                    // third parties
                    'node_modules/datatables.net/js/jquery.dataTables.js',
                    'node_modules/datatables.net-bs/js/dataTables.bootstrap.js',
                    'node_modules/datatables.net-responsive/js/dataTables.responsive.js',
                    'node_modules/datatables.net-responsive-bs/js/responsive.bootstrap.js',
                    // custom
                    'assets/src/js/datatables/base.js',
                ],
            },
            formsBase: {
                dest: '.tmp/js/forms.base.js',
                src: [
                    // third parties
                    'node_modules/eonasdan-bootstrap-datetimepicker/build/js/bootstrap-datetimepicker.min.js',
                    'node_modules/select2/dist/js/select2.js',
                    'node_modules/dropzone/dist/dropzone.js',
                    'node_modules/switchery/dist/switchery.js',
                    // custom
                    'assets/src/js/forms/base.js',
                ],
            },
            formsSuperAdmin: {
                dest: '.tmp/js/forms.superadmin.js',
                src: [
                    'assets/src/js/forms/client/superadmin.js',
                    'assets/src/js/forms/invoice/superadmin.js',
                ],
            },
            vendor: {
                dest: '.tmp/css/vendor.css',
                src: [
                    '.tmp/css/bootstrap.css',
                    'node_modules/animate.css/animate.css',
                    '.tmp/css/fontawesome.css',
                    'node_modules/toastr/build/toastr.css',
                ],
            },
        },
        uglify: {
            options: {
                compress: true,
                output: {
                    comments: false,
                }
            },
            global: {
                files: {
                    '<%= output.global.js %>': ['<%= concat.global.dest %>'],
                }
            },
            datatables: {
                files: {
                    '<%= output.datatables.base %>': ['<%= concat.datatables.dest %>'],
                    '<%= output.datatables.superAdmin %>': ['assets/src/js/datatables/superadmin.js'],
                }
            },
            forms: {
                files: {
                    '<%= output.forms.base %>': ['<%= concat.formsBase.dest %>'],
                    '<%= output.forms.superAdmin %>': ['<%= concat.formsSuperAdmin.dest %>'],
                }
            },
            highcharts: {
                files: {
                    '<%= output.highcharts.superAdmin %>': ['assets/src/js/highcharts/superadmin.js'],
                }
            }
        },
        cssmin: {
            vendor: {
                files: {
                    '<%= output.vendor.css %>': ['<%= concat.vendor.dest %>'],
                }
            },
        },
        jshint: {
            options: {
                jshintrc: '.jshintrc',
            },
            beforebuild: ['gruntfile.js', 'assets/src/js/**/*.js'],
        },
        sass: {
            options: {
                implementation: sass,
                precision: 10,
                sourceMap: false,
                outputStyle: 'compressed',
            },
            global: {
                files: {
                    '<%= output.global.css %>': 'assets/src/css/style.scss',
                }
            },
            bootstrap: {
                options: {
                    update: true,
                    precision: 10,
                    sourcemap: 'none',
                    style: 'compressed',
                },
                files: {
                    '.tmp/css/bootstrap.css': 'assets/src/css/vendors/bootstrap.scss',
                }
            },
            fontawesome: {
                options: {
                    update: true,
                    precision: 10,
                    sourcemap: 'none',
                    style: 'compressed',
                },
                files: {
                    '.tmp/css/fontawesome.css': 'node_modules/font-awesome/scss/font-awesome.scss',
                }
            },
        },
        copy: {
            fontawesome: {
                files: [{
                    expand: true,
                    cwd: 'node_modules/font-awesome/fonts/',
                    src: [
                        'fontawesome-webfont.{eot,svg,ttf,woff,woff2}',
                        'FontAwesome.otf',
                    ],
                    dest: 'assets/fonts/',
                    filter: 'isFile'
                }, ],
            },
            jquery: {
                files: [{
                    expand: true,
                    cwd: 'node_modules/jquery/dist/',
                    src: ['jquery.min.js'],
                    dest: 'assets/js/',
                    filter: 'isFile',
                }, ],
            },
            jqueryui: {
                files: [{
                    expand: true,
                    cwd: 'node_modules/jquery-ui-dist/',
                    src: ['jquery-ui.min.js'],
                    dest: 'assets/js/',
                    filter: 'isFile',
                }, ],
            },
            highcharts: {
                files: [{
                    expand: true,
                    cwd: 'node_modules/highcharts/',
                    src: ['highcharts.js'],
                    dest: 'assets/js/',
                    filter: 'isFile',
                }, ]
            },
            toastr: {
                files: [{
                    expand: true,
                    cwd: 'node_modules/toastr/build/',
                    src: ['toastr.min.js', 'toastr.js.map'],
                    dest: 'assets/js/',
                    filter: 'isFile',
                }, ]
            },
            sweetalert: {
                files: [{
                    expand: true,
                    cwd: 'node_modules/sweetalert/dist/',
                    src: ['sweetalert.min.js'],
                    dest: 'assets/js/',
                    filter: 'isFile',
                }, ]
            },
            switchery: {
                files: [{
                    expand: true,
                    cwd: 'node_modules/switchery/dist/',
                    src: ['switchery.css'],
                    dest: '.tmp/',
                    filter: 'isFile',
                    rename: function(dest, src) {
                        return dest + 'switchery.scss';
                    }
                }, ]
            },
            select2: {
                files: [{
                    expand: true,
                    cwd: 'node_modules/select2/dist/js/',
                    src: ['select2.min.js'],
                    dest: 'assets/js/',
                    filter: 'isFile',
                }, ]
            },
        },
        clean: {
            global: [
                '<%= output.global.css %>',
                '<%= output.global.js %>',
                // jquery
                'assets/js/jquery.min.js',
                // jquery-ui
                'assets/js/jquery-ui.min.js',
                // toastr
                'assets/js/toastr.min.js',
                'assets/js/toastr.js.map',
                // sweetalert
                'assets/js/sweetalert.min.js',
            ],
            highcharts: '<%= output.highcharts %>',
            fontawesome: [
                '<%= output.fontawesome.css %>',
                'assets/fonts/',
            ],
            forms: '<%= output.forms %>',
            vendor: [
                '<%= output.vendor.css %>',
            ],
            datatables: '<%= output.datatables %>',
            tmp: [
                '.tmp'
            ],
        },
        watch: {
            config: {
                files: [
                    'gruntfile.js',
                    'package.json',
                ],
                tasks: [
                    'default'
                ],
                options: {
                    reload: true,
                }
            },
            jsGlobal: {
                files: [
                    'assets/src/js/functions.js',
                    'assets/src/js/app.js',
                    'assets/src/js/global.js',
                ],
                tasks: [
                    'jshint:beforebuild',
                    'copy',
                    'concat:global',
                    'uglify:global',
                    'beep',
                ],
                options: {
                    debounceDelay: 2000,
                },
            },
            form: {
                files: [
                    'assets/src/js/forms/**/*.js',
                ],
                tasks: [
                    'taskForms',
                    'beep',
                ],
                options: {
                    debounceDelay: 2000,
                },
            },
            highcharts: {
                files: [
                    'assets/src/js/highcharts/**/*.js',
                ],
                tasks: [
                    'taskHighcharts',
                    'beep',
                ],
            },
            datatables: {
                files: [
                    'assets/src/js/datatables/**/*.js',
                ],
                tasks: [
                    'taskDatatables',
                    'beep',
                ],
            },
            cssApp: {
                files: [
                    'assets/src/css/**/*.scss',
                ],
                tasks: [
                    'sass:global',
                    'beep',
                ],
                options: {
                    debounceDelay: 2000,
                },
            },
        },
    });

    // plugins
    require('load-grunt-tasks')(grunt);

    // default task
    grunt.registerTask('default', [
        'clean',
        'taskGlobal',
        'taskForms',
        'taskVendor',
        'taskHighcharts',
        'taskDatatables',
        'beep',
    ]);

    grunt.registerTask('taskGlobal', [
        // remove old files
        'clean:global',
        // prep:both
        'copy:jquery',
        'copy:jqueryui',
        'copy:toastr',
        'copy:sweetalert',
        'copy:switchery',
        'copy:select2',
        'jshint:beforebuild',
        // build:css
        'sass:global',
        // build:js
        'concat:global',
        'uglify:global',
        // clean:both
        'header:global',
    ]);

    grunt.registerTask('taskForms', [
        // remove old files
        'clean:forms',
        // prep:both
        'jshint:beforebuild',
        // build:both
        'concat:formsBase',
        'concat:formsSuperAdmin',
        'uglify:forms',
        // clean:js
        'header:forms',
    ]);

    grunt.registerTask('taskVendor', [
        // remove old files
        'clean:vendor',
        // prep:css
        'sass:bootstrap',
        'taskPrepFontawesome',
        // build:css
        'concat:vendor',
        'cssmin:vendor',
        // clean:css
        'header:vendor',
    ]);

    grunt.registerTask('taskPrepFontawesome', [
        // remove old files
        'clean:fontawesome',
        // prep:both
        'copy:fontawesome',
        // build:css
        'sass:fontawesome',
    ]);

    grunt.registerTask('taskHighcharts', [
        // remove old files
        'clean:highcharts',
        // prep:js
        'copy:highcharts',
        'jshint:beforebuild',
        // build:js
        'uglify:highcharts',
        // clean:js
        'header:highcharts'
    ]);

    grunt.registerTask('taskDatatables', [
        // remove old files
        'clean:datatables',
        // prep:js
        'jshint:beforebuild',
        // build:js
        'concat:datatables',
        'uglify:datatables',
        // clean:js
        'header:datatables'
    ]);
};
