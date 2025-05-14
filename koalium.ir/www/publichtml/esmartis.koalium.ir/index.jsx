import React, { useState } from 'react';
import axios from 'axios';

const AuthPage = () => {
  const [isRegistering, setIsRegistering] = useState(false);
  const [emailOrPhone, setEmailOrPhone] = useState('');
  const [password, setPassword] = useState('');
  const [verificationCode, setVerificationCode] = useState('');

  const handleSubmit = async (e) => {
    e.preventDefault();

    if (isRegistering) {
      // Send verification code to email/phone
      await axios.post('/api/send-verification', { emailOrPhone });
      alert('Verification code sent!');
    } else {
      // Sign in with email/phone and password
      const response = await axios.post('/api/signin', { emailOrPhone, password });
      alert(`Welcome, ${response.data.user.name}`);
    }
  };

  const handleThirdPartyAuth = (provider) => {
    window.location.href = `/api/auth/${provider}`; // Redirect to provider's OAuth page
  };

  return (
    <div>
      <h1>{isRegistering ? 'Register' : 'Sign In'}</h1>
      <form onSubmit={handleSubmit}>
        <input
          type="text"
          placeholder="Email or Phone"
          value={emailOrPhone}
          onChange={(e) => setEmailOrPhone(e.target.value)}
          required
        />
        {isRegistering && (
          <input
            type="password"
            placeholder="Password"
            value={password}
            onChange={(e) => setPassword(e.target.value)}
            required
          />
        )}
        <button type="submit">{isRegistering ? 'Register' : 'Sign In'}</button>
      </form>

      <button onClick={() => setIsRegistering(!isRegistering)}>
        {isRegistering ? 'Already have an account? Sign In' : 'Need an account? Register'}
      </button>

      <div>
        <button onClick={() => handleThirdPartyAuth('google')}>Sign in with Google</button>
        <button onClick={() => handleThirdPartyAuth('facebook')}>Sign in with Facebook</button>
        <button onClick={() => handleThirdPartyAuth('apple')}>Sign in with Apple</button>
      </div>
    </div>
  );
};

export default AuthPage;