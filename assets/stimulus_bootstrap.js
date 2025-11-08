import { startStimulusApp } from '@symfony/stimulus-bundle';

const app = startStimulusApp();
// register any custom, 3rd party controllers here
console.log('Stimulus app started ' + app.name);
// app.register('some_controller_name', SomeImportedController);
