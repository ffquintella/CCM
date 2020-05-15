using System.Collections.Generic;
using System.Security.Claims;
using Domain;
using Microsoft.AspNetCore.Authorization.Infrastructure;

namespace CCM_API.Security
{
    public class ClaimManager
    {
        public ClaimManager(UserGroupManager userGroupManager, RoleManager roleManager)
        {
            this.userGroupManager = userGroupManager;
            this.roleManager = roleManager;
        }

        private readonly RoleManager roleManager;
        private readonly UserGroupManager userGroupManager;
        public List<Claim> GetUserClaims(User user)
        {
            const string Issuer = "https://ccm.domain";
            var claims = new List<Claim>();
            claims.Add(new Claim(ClaimTypes.Name, user.Name, ClaimValueTypes.String, Issuer));
            claims.Add(new Claim(ClaimTypes.PrimarySid, user.Id.ToString(), ClaimValueTypes.Double, Issuer));
            claims.Add(new Claim(ClaimTypes.Sid, user.AccountId.ToString(), ClaimValueTypes.Double, Issuer));
            
            
            var groups = userGroupManager.GetGroupsOfUser(user);

            foreach (var userGroup in groups)
            {
                claims.Add(new Claim(ClaimTypes.GroupSid, userGroup.Id.ToString(), ClaimValueTypes.Double, Issuer));
                foreach (var roleId in userGroup.RolesIds)
                {
                    var role = roleManager.FindById(roleId);
                    
                    claims.Add(new Claim(ClaimTypes.Role, role.Name, ClaimValueTypes.String, Issuer));
                    foreach (var claim in role.Claims)
                    {
                        claims.Add(new Claim(ClaimTypes.AuthorizationDecision, claim.Name, ClaimValueTypes.String, Issuer));
                    }
                }
            }
            
            return claims;

        }
        
        public List<string> GetUserRoles(List<Claim> claims)
        {
            var result = new List<string>();
            foreach (var claim in claims)
            {
                if (claim.Type == ClaimTypes.Role)
                {
                    result.Add(claim.Value);
                }
            }

            return result;
        }
        public List<string> GetUserRoles(User user)
        {
            var claims = GetUserClaims(user);
            return GetUserRoles(claims);
        }
    }
    

}