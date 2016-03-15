/* Danish initialisation for the jQuery UI date picker plugin. */

(function( factory ) {
	if ( typeof define === "function" && define.amd ) {

		// AMD. Register as an anonymous module.
		define([ "../datepicker" ], factory );
	} else {

		// Browser globals
		factory( jQuery.datepicker );
	}
}(function( datepicker ) {

datepicker.regional['cy'] = {
	closeText: 'Cau',
	prevText: '&#x3C;',
	nextText: '&#x3E;',
	currentText: 'Heddiw',
	monthNames: ['Ionawr','Chwefror','Mawrth','Ebrill','Mai','Mehefin',
	'Gorffennaf','Awst','Medi','Hydref','Tachwedd','Rhagfyr'],
	monthNamesShort: ['Ion','Chw','Maw','Ebr','Mai','Meh',
	'Gor','Aws','Med','Hyd','Tac','Rha'],
	dayNames: ['Dydd Sul','Dydd Llun','Dydd Mawrth','Dydd Mercher','Dydd Iau','Dydd Gwener','Dydd Sadwrn'],
	dayNamesShort: ['Sul','Llu','Maw','Mer','Iau','Gwe','Sad'],
	dayNamesMin: ['Su','Ll','Ma','Me','Ia','Gw','Sa'],
	weekHeader: 'Wyth',
	dateFormat: 'dd/mm/yy',
	firstDay: 1,
	isRTL: false,
	showMonthAfterYear: false,
	yearSuffix: ''};
datepicker.setDefaults(datepicker.regional['cy']);

return datepicker.regional['cy'];

}));
