module.exports = function ( grunt ) {

    // Project configuration.
    grunt.initConfig( {
        pkg: grunt.file.readJSON( 'package.json' ),

        jshint: {
            all: {
                src: [ 'module/Application/view/layout/admin/src/js/**/*.js' ],
                options: {}
            }
        },

        sass: {
            options: {
                //sourceMap: true,
                outputStyle: 'compressed'
            },
            dist: {
                files: {
                    'public/css/admin/theme.min.css': ['module/Application/view/layout/admin/src/sass/themes/default/*.scss'],
                    'public/css/admin/main.min.css': ['module/Application/view/layout/admin/src/sass/main.scss']
                }
            }
        },
    /*
        cssmin: {
            options: {
                keepSpecialComments: 0,
            },
            libs: {
                src: ['module/Application/view/layout/admin/src/css/libs/*.css'],
                dest: 'public/css/admin/libs.min.css'
            }

        },
        */
        cssmin: {
            options: {
                mergeIntoShorthands: false,
                roundingPrecision: -1,

            },
            target: {
                files: {
                    'public/css/admin/libs.min.css': ['module/Application/view/layout/admin/src/css/libs/bootstrap.css', 'module/Application/view/layout/admin/src/css/libs/datetimepicker.css']
                }
            }
        },
        /*
        concat: {
          libs: {
            src:  ['module/Application/view/layout/admin/src/js/libs/jquery-3.1.1.min.js', 'module/Application/view/layout/admin/src/js/libs/angular.js', 'module/Application/view/layout/admin/src/js/libs/bootstrap.js'],
            dest: 'public/js/libs.min.js'
          }
        },
*/
        uglify: {

            libs: {
             src:  ['module/Application/view/layout/admin/src/js/libs/jquery/*.js', 'module/Application/view/layout/admin/src/js/libs/flot/*.js', 'module/Application/view/layout/admin/src/js/libs/*.js'],
             dest: 'public/js/libs.min.js'
            },


            classes: {
                src:  'module/Application/view/layout/admin/src/js/classes/**/*.js',
                dest: 'public/js/classes.min.js'
            },

            modules: {
                src:  'module/Application/view/layout/admin/src/js/modules/**/*.js',
                dest: 'public/js/modules.min.js'
            },

            init: {
                src:  'module/Application/view/layout/admin/src/js/*.js',
                dest: 'public/js/init.min.js'
            }

        },
        watch: {
            dev: {
                options: {
                    spawn: false,
                    interrupt: true,
                    interval: 500
                },
                files: ['module/Application/view/layout/admin/src/**/*'],
                tasks: ['dev']
            }
        }

    } );

    // Load the plugin that provides the "uglify" task.
    grunt.loadNpmTasks( 'grunt-contrib-uglify' );
    grunt.loadNpmTasks( 'grunt-sass' );
    grunt.loadNpmTasks( 'grunt-contrib-cssmin' );
    grunt.loadNpmTasks('grunt-contrib-watch');
    grunt.loadNpmTasks('grunt-concat');

    // Default task(s).
    grunt.registerTask( 'dev', [ 'uglify', 'sass', 'cssmin', 'watch' ] );

};