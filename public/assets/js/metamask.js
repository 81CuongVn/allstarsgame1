window.onload = init();
function init() {
	verifyAllMetamask();
	getCurrentBalance();
}

async function verifyAllMetamask() {
	const metamaskApi	= ethereum.isMetaMask;
	if (!metamaskApi) {
		jalert('Please connect MetaMask Wallet');
		return;
	}

	const conected		= ethereum.isConnected();
	if (!conected) {
		jalert('Please connect BSC Wallet');
		return;
	}

	const chainId = await ethereum.request({
		method: 'eth_chainId'
	});

	if (appState != 'dev' && chainId != '0x38') {
		// Switch to mainnet
		try {
			await ethereum.request({
				method: 'wallet_switchEthereumChain',
				params: [{
					chainId: '0x38'
				}],
			});
		} catch (switchError) {
			// This error code indicates that the chain has not been added to MetaMask.
			if (switchError.code === 4902) {
				try {
					await ethereum.request({
						method: 'wallet_addEthereumChain',
						params: [{
							chainId: '0x38',
							chainName: 'Smart Chain',
							nativeCurrency: {
								name: 'Binance Coin',
								symbol: 'BNB', // 2-6 characters long
								decimals: 18
							},
							rpcUrl: 'https://bsc-dataseed.binance.org/',
							blockExplorerUrls: 'https://bscscan.com'
						}],
					});
				} catch (addError) {
					// handle "add" error
					// console.error(addError);
				}
			}
			// handle other "switch" errors
		}
	} else {
		// Switch to testnet
		try {
			await ethereum.request({
				method: 'wallet_switchEthereumChain',
				params: [{
					chainId: '0x61'
				}],
			});
		} catch (switchError) {
			// This error code indicates that the chain has not been added to MetaMask.
			if (switchError.code === 4902) {
				try {
					await ethereum.request({
						method: 'wallet_addEthereumChain',
						params: [{
							chainId: '0x61',
							chainName: 'Smart Chain - Testnet',
							nativeCurrency: {
								name: 'Binance Coin',
								symbol: 'BNB', // 2-6 characters long
								decimals: 18
							},
							rpcUrl: 'https://data-seed-prebsc-1-s1.binance.org:8545/',
							blockExplorerUrls: 'https://testnet.bscscan.com'
						}],
					});
				} catch (addError) {
					// handle "add" error
					// console.error(addError);
				}
			}
			// handle other "switch" errors
		}
	}

	const accounts = await window.ethereum.request({
		method: 'eth_requestAccounts'
	});
	const account = accounts[0];
	verifyIfHaveTokenContract();

	window.ethereum.on('accountsChanged', function(accounts) {
		// Time to reload your interface with accounts[0]!
		if (haveWallet == null) {
			ajaxToAccount(accounts[0]);
		}
	});
}


async function verifyIfHaveTokenContract() {
	// ethereum
	// 	.request({
	// 		method:	'wallet_watchAsset',
	// 		params:	{
	// 			type:		'BEP20',
	// 			options:	{
	// 				address:	tokenAddress.address,
	// 				symbol:		tokenAddress.token,
	// 				decimals:	18,
	// 				image:		image_url('logo.png'),
	// 			}
	// 		}
	// 	}).then((success) => {
	// 		if (success) {
	// 			return true;
	// 		} else {
	// 			throw new Error('Something went wrong.')
	// 		}
	// 	}).catch(console.error)
}

function ajaxToAccount(address) {
	$.ajax({
		url:		make_url('users#metamask'),
		type:		'POST',
		data:		{ wallet: address },
		dataTypee:	'json',
		success:	function(result) {
			if (!result.success) {
				format_error(result);
				return;
			} else {
				window.location.reload();
			}
		},
		error:		function(xhr, ajaxOptions, thrownError) {
			jalert(xhr.responseText);
			return;
		}
	});
}

function changeBNBBalancesInDOM(bnb) {
	if (document.getElementById("bnbBalance")) {
		document.getElementById("bnbBalance").innerText = bnb;
	}
}

function changeTOKENBalancesInDOM(token) {
	if (document.getElementById("tokenBalance")) {
		document.getElementById("tokenBalance").innerText = token;
	}
}

async function getTokenBalance() {
	web3			= new Web3(window.ethereum);
	const tokenABI	= await $.getJSON(absolute_url('tokenABI.json'));
	const tokenInst	= new web3.eth.Contract(tokenABI, tokenAddress.address);

	await tokenInst.methods.balanceOf(haveWallet).call().then((balance) => {
		const balanceFormated = ((parseInt(balance) / 1000000000) / 1000000000).toFixed(2);
		changeTOKENBalancesInDOM(balanceFormated);
	}).catch((err) => {
		changeTOKENBalancesInDOM(0);
	});
}

function getCurrentBalance() {
	if (haveWallet === null) {
		return;
	}

	ethereum
		.request({
			method: 'eth_getBalance',
			params: [ haveWallet, 'latest' ]
		})
		.then((balances) => {
			changeBNBBalancesInDOM((parseInt(balances, 16) / 1000000000) / 1000000000);
			getTokenBalance();
		})
		.catch((err) => {
			if (err.code === 4001) {
				// EIP-1193 userRejectedRequest error
				// If this happens, the user rejected the connection request.
				jalert("Please connect to MetaMask.");
				return;
			} else {
				console.error(err);
			}
		});

}

async function callMetaToken(amount, address, id) {
	// Sending Ethereum to an address
	web3			= new Web3(web3.currentProvider);
	amount			= web3.utils.toBN(amount).toString();

	const tokenABI	= await $.getJSON(absolute_url('tokenABI.json'));
	const tokenInst	= new web3.eth.Contract(tokenABI, tokenAddress.address);

	const response	= await tokenInst.methods
		.transfer(address, amount) // function in contract
		.send({
			from: haveWallet,
			to: address,
		});

	if (response.status == true) {
		console.log(response.transactionHash, id); // Deprecated
	} else {
		jalert('Error: ' + response);
		return;
	}

}

///////////////////////////////////////////////////////////////////////////////////////////////////////////////

async function loginMetamask() {
	const provider	= await detectEthereumProvider();
	if (!provider) {
		jalert("Please install MetaMask!");
		return;
	}
	startApp(provider);
}

async function startApp(provider) {
	if (haveWallet !== null) {
		connect();
	}

	if (provider !== window.ethereum) {
		jalert("Do you have multiple wallets installed?");
		return;
	}

	const chainId = await ethereum.request({
		method: "eth_chainId"
	});
	handleChainChanged(chainId);
	currentChain = chainId;
	console.log("Chain ID:", chainId);

	ethereum.request({
		method: "eth_accounts"
	}).then(handleAccountsChanged).catch((err => {
		console.error(err)
	}))
}

function connect() {
	ethereum.request({
		method: "eth_requestAccounts"
	}).then((e => {
		if (0 === e.length) {
			jalert("Please connect to MetaMask.");
			return;
		} else {
			let n = e[0];
			ajaxToAccount(n)
		}
	})).catch((e => {
		if (e.code === 4001) {
			jalert("Please connect to MetaMask.");
			return;
		} else {
			console.error(e)
		}
	}))
}

function handleChainChanged(e) {
	getCurrentBalance()
}

function handleAccountsChanged(accounts) {
	console.log(accounts);
	if (accounts.length === 0) {
		jalert("Please connect to MetaMask.");
		return;
	} else {
		if (accounts[0] !== currentAccount) {
			currentAccount = accounts[0];
			ajaxToAccount(currentAccount);
		}
	}
}

ethereum.on("chainChanged",		handleChainChanged);
ethereum.on("accountsChanged",	handleAccountsChanged);
