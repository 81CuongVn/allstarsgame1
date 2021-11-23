window.onload = init();

function init() {
	verifyAllMetamask();
	if (haveWallet) {
		getCurrentBalance();
	}
}

async function verifyAllMetamask() {
	const conected		= ethereum.isConnected();

	const metamaskApi	= ethereum.isMetaMask;
	if (!metamaskApi) {
		alert('Please connect metamask Wallet');
	}

	if (!conected) {
	    alert('Please connect BSC Wallet');
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
					console.error(addError);
				}
			}
			// handle other "switch" errors
		}

		const accounts = await window.ethereum.request({
			method: 'eth_requestAccounts'
		});
		const account = accounts[0];
		console.log(account);

		window.ethereum.on('accountsChanged', function(accounts) {
			// Time to reload your interface with accounts[0]!
			// console.log(accounts[0])
			if (haveWallet == null) {
				ajaxToAccount(accounts[0]);
			}
		});
	}
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
	web3				= new Web3(window.ethereum);
	const tokenAddress	= {
		address: "0xB508EC43B75e4869E247c19c2C05158d90f4f99D",
		token: "AASG"
	};

	const tokenABI		= await $.getJSON(absolute_url('tokenABI.json'));
	const tokenInst		= new web3.eth.Contract(tokenABI, tokenAddress.address);

	await tokenInst.methods.balanceOf(haveWallet).call().then((balance) => {
		const balanceFormated = ((parseInt(balance) / 1000000000) / 1000000000).toFixed(2);
		changeTOKENBalancesInDOM(balanceFormated);
	}).catch((err) => {
		changeTOKENBalancesInDOM(0);
	});
}

function getCurrentBalance() {
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
				console.log('Please connect to MetaMask.');
			} else {
				console.error(err);
			}
		});

}

///////////////////////////////////////////////////////////////////////////////////////////////////////////////

async function loginMetamask() {
	const e = await detectEthereumProvider();
	e ? startApp(e) : console.log("Please install MetaMask!")
}

async function startApp(e) {
	null !== haveWallet && connect(), e !== window.ethereum && console.error("Do you have multiple wallets installed?");
	const n = await ethereum.request({
		method: "eth_chainId"
	});
	handleChainChanged(n), currentChain = currentChain, console.log("Chain", n), ethereum.request({
		method: "eth_accounts"
	}).then(handleAccountsChanged).catch((e => {
		console.error(e)
	}))
}

function ajaxToAccount(address) {
	$.ajax({
		type: 'POST',
		url: make_url('users#metamask'),
		data: { wallet: address },
		success: function(data) {
			console.log(data);
			// if (data.error != undefined) {
			// 	alert('Error: ' + data.error)
			// } else {
			// 	window.location.reload();
			// }
		},
		error: function(xhr, ajaxOptions, thrownError) {
			alert(xhr.responseText);
		}
	});
}

function connect() {
	ethereum.request({
		method: "eth_requestAccounts"
	}).then((e => {
		if (0 === e.length) console.log("Please connect to MetaMask.");
		else {
			let n = e[0];
			ajaxToAccount(n)
		}
	})).catch((e => {
		4001 === e.code ? console.log("Please connect to MetaMask.") : console.error(e)
	}))
}

function handleChainChanged(e) {
	getCurrentBalance()
}

function handleAccountsChanged(e) {
	0 === e.length ? console.log("Please connect to MetaMask.") : e[0] !== currentAccount && (currentAccount = e[0], ajaxToAccount(currentAccount))
}

ethereum.on("chainChanged", handleChainChanged);
ethereum.on("accountsChanged", handleAccountsChanged);
