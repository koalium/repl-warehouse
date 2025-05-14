// server.js
const express = require('express');
const axios = require('axios');
const bodyParser = require('body-parser');
const { v4: uuidv4 } = require('uuid');

const app = express();
app.use(bodyParser.json());

// In-memory "database" for demo purposes
// Replace with real database in production
const db = {
    users: {},
    transactions: {}
};

// Configuration
const MY_WALLET = 'txjfylgf41641365431541';
const TRONSCAN_API = 'https://apilist.tronscan.org/api/transaction-info';

// Middleware to validate wallet address
function validateWallet(req, res, next) {
    const wallet = req.query.wallet || req.body.wallet;
    if (!wallet) {
        return res.status(400).json({ error: 'Wallet address required' });
    }
    next();
}

// Dashboard endpoint
app.get('/api/dashboard', validateWallet, (req, res) => {
    const wallet = req.query.wallet;
    
    // Get user data from database
    const userData = db.users[wallet] || {
        transactions: [],
        assets: [],
        stats: {
            profit: '0%',
            volume: '$0',
            trades: '0'
        }
    };
    
    res.json(userData);
});

// Transaction verification endpoint
app.post('/api/verify-tx', validateWallet, async (req, res) => {
    const { wallet, txHash } = req.body;
    
    try {
        // 1. Check if transaction already exists
        const existingTx = Object.values(db.transactions).find(
            tx => tx.hash === txHash && tx.wallet === wallet
        );
        
        if (existingTx) {
            return res.status(400).json({ 
                error: 'Transaction already recorded' 
            });
        }
        
        // 2. Verify with Tronscan API
        const response = await axios.get(`${TRONSCAN_API}?hash=${txHash}`);
        const txData = response.data;
        
        if (!txData.hash) {
            return res.status(400).json({ 
                error: 'Transaction not found on TRON network' 
            });
        }
        
        // 3. Validate transaction details
        if (txData.transferToAddress !== MY_WALLET) {
            return res.status(400).json({ 
                error: 'Transaction not sent to our wallet' 
            });
        }
        
        const amount = (txData.amount / 1000000).toFixed(2);
        const date = new Date(txData.timestamp).toISOString().split('T')[0];
        const isConfirmed = txData.confirmed;
        
        // 4. Create transaction record
        const newTx = {
            id: uuidv4(),
            wallet,
            hash: txHash,
            amount: `${amount} TRX`,
            date,
            status: isConfirmed ? 'Completed' : 'Pending',
            confirmed: isConfirmed,
            timestamp: Date.now()
        };
        
        // 5. Update database
        db.transactions[newTx.id] = newTx;
        
        // Initialize user if not exists
        if (!db.users[wallet]) {
            db.users[wallet] = {
                transactions: [],
                assets: [],
                stats: {
                    profit: '0%',
                    volume: '$0',
                    trades: '0'
                }
            };
        }
        
        // Add transaction to user
        db.users[wallet].transactions.unshift(newTx);
        
        // Update assets
        const trxAsset = db.users[wallet].assets.find(a => a.symbol === 'TRX');
        const trxValue = (parseFloat(amount) * 0.10).toFixed(2); // Sample valuation
        
        if (trxAsset) {
            trxAsset.amount = (parseFloat(trxAsset.amount) + parseFloat(amount)).toFixed(2);
            trxAsset.value = `$${(parseFloat(trxAsset.value.replace('$','')) + parseFloat(trxValue)).toFixed(2)}`;
        } else {
            db.users[wallet].assets.push({
                symbol: 'TRX',
                amount: amount,
                value: `$${trxValue}`
            });
        }
        
        // Update stats
        db.users[wallet].stats.trades = (parseInt(db.users[wallet].stats.trades) )+ 1;
        db.users[wallet].stats.volume = `$${(parseFloat(db.users[wallet].stats.volume.replace('$',''))) + parseFloat(amount)}`;
        
        // 6. Return success
        res.json({
            success: true,
            message: 'Transaction verified and added',
            amount: amount
        });
        
    } catch (error) {
        console.error('Verification error:', error);
        res.status(500).json({ 
            error: error.response?.data?.message || error.message 
        });
    }
});

// Start server
const PORT = process.env.PORT || 3000;
app.listen(PORT, () => {
    console.log(`Server running on port ${PORT}`);
});