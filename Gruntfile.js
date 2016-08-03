// Example of Grunt tasks configuration.

module.exports = function(grunt) {
    'use strict';

    // load all required dependencies
    require('matchdep').filterDev('grunt-*').forEach(grunt.loadNpmTasks);

    grunt.initConfig({
        pkg: grunt.file.readJSON('package.json'),

        // copy component files
        copy: {
           components: {
                expand: true,
                cwd: 'node_modules/',
                src: [
                    'jquery/dist/**',
                    'font-awesome/css/**',
                    'font-awesome/fonts/**',
                    'font-awesome/less/**',
                    'bootstrap/dist/**',
                    'bootstrap/fonts/**',
                    'bootstrap/less/**',
                ],
                dest: 'assets/components'
            }
        },

        // minify css source file
        cssmin: {
            options: {
                sourceMap: true,
            },
            target: {
                files: {
                   'assets/css/dist/style.min.css': 'assets/css/style.css'
                }
            }
        },

        // watch respective tasks
        watch: {
            css: {
                files: ['assets/css/style.css'],
                tasks: ['cssmin']
            }
        }
    });

    grunt.registerTask('default', 'Run configured tasks.', ['copy', 'cssmin']);
};
