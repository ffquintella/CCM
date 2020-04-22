using System;
using System.Net;
using CCM_API.Helpers;
using CCM_API.Security;
using Domain.Authentication;
using Microsoft.Extensions.Configuration;
using Serilog;

namespace CCM_API
{
    public class AuthenticationManager: BaseManager
    {
        public AuthenticationManager(
            IgniteManager igniteManager,
            IConfiguration configuration, 
            AccountManager accountManager,
            UserManager userManager,
            ClaimManager claimManager): base(igniteManager, configuration)
        {
            this.accountManager = accountManager;
            this.userManager = userManager;
            this.claimManager = claimManager;

        }
        
        private readonly AccountManager accountManager;
        private readonly UserManager userManager;
        private readonly ClaimManager claimManager;

        public Tuple<AuthenticationErrorType, AuthenticationData> ExecuteAuthentication(AuthenticationRequest request, IPAddress ipAddress, string login)
        {
            var result = new Tuple<AuthenticationErrorType,AuthenticationData>(AuthenticationErrorType.UnkwonError, null);

            var account = accountManager.FindByLogin(request.Login);

            if (account == null)
            {
                result = new Tuple<AuthenticationErrorType,AuthenticationData>(AuthenticationErrorType.LoginDoesntExists, null);
                return result;
            }
            
            if (PasswordTool.VerifyHashedPassword(account.GetPassword(), request.Password))
            {
                var authCache = igniteManager.GetIgnition().GetOrCreateCache<string, AuthenticationData>("AuthenticationControl");

                var user = userManager.FindUserByAccountId(account.Id);
                var claims = claimManager.GetUserClaims(user);
                var roles = claimManager.GetUserRoles(claims);
                
                var authData = new AuthenticationData()
                {
                    Status = AuthenticationStatus.OK,
                    ErrorType = AuthenticationErrorType.NoError,
                    IpAuthorized = ipAddress.ToString(),
                    Login = login,
                    Token = TokenHelper.GenerateToken(ipAddress, login),
                    Claims = claims,
                    Roles = roles
                };
                
                authCache.Put(authData.Token, authData);
                
                result = new Tuple<AuthenticationErrorType,AuthenticationData>(AuthenticationErrorType.NoError, authData);

            }
            else
            {
                result = new Tuple<AuthenticationErrorType,AuthenticationData>(AuthenticationErrorType.BadPassword, null);
                return result;
            }

            return result;
        }

        public AuthenticationData GetAuthenticationData(string token)
        {
            var authCache = igniteManager.GetIgnition().GetOrCreateCache<string, AuthenticationData>("AuthenticationControl");
            
            var authenticationData = authCache.Get(token);
            if (authenticationData == null) return null;;
            return authenticationData;

        }        
        public Tuple<bool, AuthenticationData> ValidateTokenWithData(string token, IPAddress ipAddress)
        {
            if (string.IsNullOrEmpty(token)) return new Tuple<bool, AuthenticationData>(false, null);
            try
            {
                var openToken = StringHelper.ConvertFomBase64(token);

                var splitten = openToken.Split(":");
                if (splitten.Length != 3)
                {
                    return new Tuple<bool, AuthenticationData>(false, null);
                }

                var ip = splitten[1];
                //var entropy = splitten[2];

                if (ipAddress.ToString() != ip) return new Tuple<bool, AuthenticationData>(false, null);

                var authCache = igniteManager.GetIgnition()
                    .GetOrCreateCache<string, AuthenticationData>("AuthenticationControl");

                try
                {
                    var authenticationData = authCache.Get(token);
                    if (authenticationData == null) return new Tuple<bool, AuthenticationData>(false, null);
                    ;

                    if (authenticationData.Token != token) return new Tuple<bool, AuthenticationData>(false, null);
                    if (authenticationData.IpAuthorized != ipAddress.ToString())
                        return new Tuple<bool, AuthenticationData>(false, null);
                    Log.Information("Token verified for ip:{0}", ipAddress.ToString());
                    return new Tuple<bool, AuthenticationData>(true, authenticationData);
                }
                catch (System.Collections.Generic.KeyNotFoundException exception)
                {
                    Log.Information("Invalid token verification from:{0}", ipAddress.ToString());
                    return new Tuple<bool, AuthenticationData>(false, null);
                }
            }
            catch (Exception ex)
            {
                Log.Warning("Bad formated token:" + ex.Message);
                return new Tuple<bool, AuthenticationData>(false, null);
            }
            
        }
        
        public bool ValidateToken(string token, IPAddress ipAddress)
        {
            return ValidateTokenWithData(token, ipAddress).Item1;
        }
        
    }
}