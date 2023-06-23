var now = Math.round(new Date().getTime() / 1000);
//console.log( now );

var x_timestamp_scale = 60*60*24; // 1d

var archives_data = [];
js_data.forEach((timestamp) => {
	var x = Math.round((now - timestamp) / x_timestamp_scale); // timestamp diff scaled
	var date = new Date(timestamp*1000);
	var dateString = date.toISOString().slice(0, -5).replace('T', ' '); //TODO handle timezone shift
	archives_data.push({
		x: x,
		y: 0,
		date: dateString,
	});
});


const data = {
	datasets: [{
		label: 'archives',
		data: archives_data,
		backgroundColor: 'rgb(255, 0, 0)'
	}]
};

const config = {
	type: 'bubble',
	data: data,
	options: {
		scales: {
			x: {
				type: 'logarithmic',
				position: 'bottom',
				min: 1,
				max: (60*60*24*365*10) / x_timestamp_scale, // 10 y scaled
				ticks: {
					maxRotation: 90,
                    minRotation: 90,
                    maxTicksLimit: 30,
                    callback: function(value, index, ticks) {
						var timestamp = now - (value * x_timestamp_scale);
						var date = new Date(timestamp*1000);
						var dateString = date.toISOString().slice(0, -5).replace('T', ' '); //TODO shorter date (time) format
                        return dateString;
                    }
                }
			},
			y: {
				display: false,
			}
		},
		maintainAspectRatio: false,
		pointRadius: 5,
		plugins: {
			tooltip: {
				callbacks: {
                    label: function(context) {
                        return "";
                    },
                    title: function(context) {
                        return context[0].raw.date;
                    }
                }
			},
		}
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
