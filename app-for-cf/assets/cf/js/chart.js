
/**
 * Create the CloudflareAppChart namespace
 * @package CloudflareApp
 */

let CloudflareAppChart = {};

{
	CloudflareAppChart.Chart = function() { this.__construct(); };
	CloudflareAppChart.Chart.prototype =
		{
			charts: [],

			__construct: function()
			{
				document.addEventListener('DOMContentLoaded', () => {
					document.querySelectorAll('#cfRange input').forEach(
						(el) => {
							el.addEventListener('change', (e) => {
								this.analytics(e);
							});
						}
					);

					document.querySelector('#cfRange input').dispatchEvent(new Event('change'));
				}, false);
			},

			analytics: function(e)
			{
				if (typeof XF == 'object')
				{
					XF.ajax(
						'POST',
						document.getElementById('cfRange').dataset.href,
						{
							'range': e.target.value
						},
						this.generateCharts.bind(this)
					);
				} else {
					// Wordpress
					fetch(ajaxurl, {
						method: 'POST',
						headers: {
							'Content-Type': 'application/x-www-form-urlencoded'
						},
						body: new URLSearchParams({
							action: document.getElementById('cfRange').dataset.action,
							range: e.target.value
						})
					})
						.then(response => response.json())
						.then(this.generateCharts.bind(this));
				}
			},

			generateCharts: function(stats)
			{
				stats = stats.stats;

				document.querySelectorAll('.cfStats .displayData').forEach((e) => {
					let label = JSON.parse(e.dataset.label);
					e.querySelector('label div').innerHTML = this.formatNumber(stats.totals[JSON.parse(e.dataset.data)[0]], parseInt(e.dataset.decimals), e.dataset.type);
					let data = [];
					JSON.parse(e.dataset.data).forEach(
						(el, i) => {
							let color = JSON.parse(e.dataset.color ? e.dataset.color : '[]');
							let chart = {
								label: label[i],
								data: Object.values(stats.detail[el])
							};
							if(color && color[i])
							{
								chart.backgroundColor = color[i];
							}
							data.push(chart);
						}
					);
					this.sparkChart(e.querySelector('canvas').getAttribute('id'), Object.keys(stats.detail[JSON.parse(e.dataset.data)[0]]), data);
				});
			},

			sparkChart: function(id, xAxis, datasets)
			{
				let finalDatasets = [];

				datasets.forEach((v, i) => {
					finalDatasets[i] = {};
					Object.assign(finalDatasets[i], {
						label: '',
						backgroundColor: "rgba(34,218,200,0.7)",
						cubicInterpolationMode: "monotone",
						fill: true,
						data: []
					}, v);
				});


				if (typeof this.charts[id] == 'object')
				{
					this.charts[id].data = {
						labels: xAxis,
						datasets: finalDatasets
					};
					this.charts[id].update();
				}
				else
				{
					this.charts[id] = new Chart(id, {
						type: "line",
						data: {
							labels: xAxis,
							datasets: finalDatasets
						},
						options: {
							maintainAspectRatio: false,
							interaction: {
								mode: 'index',
								intersect: false,
							},
							scales: {
								x: {
									border: {
										width: 0
									},
									ticks: {
										display: false
									},
									grid: {
										display: false
									}
								},
								y: {
									border: {
										width: 0
									},
									ticks: {
										display: false,
									},
									grid: {
										display: false
									},
									suggestedMin: 0
								}
							},
							plugins: {
								legend: {
									display: false
								},
								tooltip: {
									callbacks: {
										label: function(context) {
											let label = context.dataset.label || '';

											if (label) {
												label += ': ';
											}
											if (context.parsed.y !== null) {

												if (id.includes("Bytes"))
												{
													label += this.formatNumber(context.parsed.y, 0, 'bytes');
												}
												else if (id.includes("Percent"))
												{
													label += this.formatNumber(context.parsed.y, 2, 'percent');
												}
												else
												{
													label += this.formatNumber(context.parsed.y, 2, 'num');
												}

											}
											return label;
										}.bind(this)
									}
								}
							}
						}
					});
				}
			},

			formatNumber: function(number, decimals = 0, type = 'bytes')
			{
				if (number == 0)
				{
					return '0';
				}

				let k = 1000
				let dm = decimals < 0 ? 0 : decimals
				if (type === 'bytes')
				{
					var sizes = [' B', ' KB', ' MB', ' GB', ' TB', ' PB', ' EB', ' ZB', ' YB'];
				}
				else if (type === 'num')
				{
					var sizes = ['', 'k', 'M', 'B', 'T'];
				}

				let i = Math.max(0, Math.floor(Math.log(number) / Math.log(k)));

				return parseFloat((number / Math.pow(k, i)).toFixed(dm)) + (typeof sizes == 'undefined' ? '' : sizes[i]) + (type === 'percent' ? '%': '');
			}
		};

	CloudflareAppChart._Chart = new CloudflareAppChart.Chart();
}