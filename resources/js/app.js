import './bootstrap';
import Swal from 'sweetalert2';

window.showAlert = function (message, type = 'success') {
	Swal.fire({
		title: type === 'success' ? 'Success' : 'Notice',
		text: message,
		width: 600,
		padding: "3em",
		color: "#716add",
		background: "rgba(0,0,0,0.85)",
		backdrop: `
    rgba(0,0,123,0.4)
    url("https://media.giphy.com/media/v1.Y2lkPTc5MGI3NjExZTh4d2txcXQxMmRla3p0bnhwdzJsM20yeXlrbTd4ejBkcHRxajZqdSZlcD12MV9naWZzX3NlYXJjaCZjdD1n/5ocAtoAPhIDcI/giphy.gif")
    left top
    no-repeat
  `
	});
};

window.confirmAction = function (message, callbackEvent) {
	Swal.fire({
		title: 'Are you sure?',
		text: message,
		icon: 'warning',
		width: 480,
		padding: '2em',
		color: '#ffffff',
		background: '#0f172a',
		showCancelButton: true,
		confirmButtonColor: '#dc2626',
		cancelButtonColor: '#334155',
		confirmButtonText: 'Yes',
		cancelButtonText: 'Cancel'
	}).then((result) => {
		if (result.isConfirmed) {
			Livewire.dispatch(callbackEvent);
		}
	});
};



