using Domain.Security;
using Domain.System;
using Microsoft.Extensions.Configuration;

namespace CCM_API
{
    public class SystemManager: BaseManager
    {
        public SystemManager(
            IgniteManager igniteManager,
            IConfiguration configuration ): base(igniteManager,configuration) { }


        
        public ConsolidatedSystemParameters GetConsolidated()
        {
            var consolidated = new ConsolidatedSystemParameters();

            consolidated.PasswordComplexity = GetSystemPasswordComplexity();

            return consolidated;
        }

        public PasswordComplexity GetSystemPasswordComplexity()
        {
            var pwdCache = igniteManager.GetIgnition().GetOrCreateCache<string, PasswordComplexity>("metaData");

            return pwdCache.Get("SysPwdComplexity");
        }
        
        public void SetSystemPasswordComplexity(PasswordComplexity sysPwdComplexity)
        {
            var pwdCache = igniteManager.GetIgnition().GetOrCreateCache<string, PasswordComplexity>("metaData");

            pwdCache.Put("SysPwdComplexity", sysPwdComplexity);
        }
        
    }
}