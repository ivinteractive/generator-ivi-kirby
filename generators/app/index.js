'use strict';
const Generator = require('yeoman-generator');
const chalk = require('chalk');
const yosay = require('yosay');
const mkdirp = require('mkdirp');
const config = require('./config.json');
const directories = config.directories;
const gitkeeps = config.gitkeeps;
const files = config.files;
const gitmodules = config.gitmodules;
const tpls = config.tpls;
const siteFolders = config.siteFolders;

module.exports = class extends Generator {

  prompting() {
    // Have Yeoman greet the user.
    this.log(yosay(
      'Welcome to the badass ' + chalk.red('IV Interactive Kirby') + ' generator!'
    ));

    const prompts = [{
      type: 'input',
      name: 'folderName',
      message: 'What should the folder be called?'
    }];

    return this.prompt(prompts).then(props => {
      // To access props later use this.props.someAnswer;
      this.props = props;
    });
  }

  writing() {

    const folderName = this.props.folderName;

    // Set up folders
    for(var d=0; d<directories.length; d++)
      mkdirp.sync(folderName+'/'+directories[d]);

    for(var s=0; s<siteFolders.length; s++){
      this.fs.copy(
        this.templatePath('site/'+siteFolders[s]),
        this.destinationPath(folderName+'/site/'+siteFolders[s])
      );
    }

    this.fs.copy(
      this.templatePath('resources'),
      this.destinationPath(folderName+'/resources')
    );

    // Add .gitkeeps for folders that start empty
    for(var g=0; g<gitkeeps.length; g++)
      this.fs.copy(
        this.templatePath('.gitkeep'),
        this.destinationPath(folderName+'/'+gitkeeps[g]+'/.gitkeep')
      );

    // Default template files
    for(var t=0; t<tpls.length; t++)
      this.fs.copy(
        this.templatePath('tpls/'+tpls[t].file),
        this.destinationPath(folderName+'/site/'+tpls[t].folder+'/'+tpls[t].destination)
      );

    // Single files to copy over
    for(var f=0; f<files.length; f++)
      this.fs.copy(
        this.templatePath(files[f]),
        this.destinationPath(folderName+'/'+files[f]),
        {
          process: function(content) {
            var regEx = new RegExp('{title}', 'g');
            var newContent = content.toString().replace(regEx, folderName);
            return newContent;
          }
        }
      );
  }

  install() {
  //   var path = process.cwd() + '/' + this.props.folderName;
  //   process.chdir(path);
  //   this.yarnInstall();
  //   this.spawnCommand('composer', ['install']);
  //   this.spawnCommandSync('git', ['init']);
  //   for(var gm=0; gm<gitmodules.length; gm++)
  //     this.spawnCommandSync('git', ['submodule', 'add', gitmodules[gm].url, gitmodules[gm].path]);
  }
};
