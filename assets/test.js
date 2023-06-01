const labels = ['2023-06-01', '2023-05-01', '2023-04-01'];

const data = {
	datasets: [{
		label: 'archives',
		data: [
			{
				x: 0,
				y: 0,
				r: 10,
				date: '2023-06-01',
			},
			{
				x: 31,
				y: 0,
				r: 10,
				date: '2023-05-01',
			},
			{
				x: 60,
				y: 0,
				r: 10,
				date: '2023-04-01'
			},
		],
		backgroundColor: 'rgb(255, 0, 0)'
	}],
	labels: labels
};

const config = {
	type: 'bubble',
	data: data,
	options: {
		scales: {
			x: {
				type: 'logarithmic',
				position: 'bottom',
			},
			y: {
				display: false,
			}
		},
		maintainAspectRatio: false
	}
};


var cv = document.getElementById('archives_chart')
var ctx = cv.getContext('2d');
var chart = new Chart(ctx, config);

cv.onclick = function(evt)
{
    var activePoint = chart.getElementsAtEventForMode(evt, 'nearest', { intersect: true }, true);
    var point_raw_data = activePoint[0].element.$context.raw;
    console.log('activePoint', point_raw_data);
    // var url = ... make link with data from activePoint
//    window.location = 'https://www.google.by/search?q=chart+js+events&oq=chart+js+events'
};
