using System;
using Apache.Ignite.Core.Cache.Expiry;
using Apache.Ignite.Core.Common;

namespace CCM_API.Factories
{
    public class ExpiryPolicyFactory: IFactory<ExpiryPolicy>
    {

        public TimeSpan TimeOutCreate { get; set; }
        public TimeSpan TimeOutUpdate { get; set; }
        public TimeSpan TimeOutAccess { get; set; }

        public ExpiryPolicyFactory(TimeSpan timeout)
        {
            TimeOutCreate = TimeOutAccess = TimeOutUpdate = timeout;
        }
        
        public ExpiryPolicyFactory(TimeSpan timeoutCreate, TimeSpan timeoutUpdate, TimeSpan timeoutAccess)
        {
            TimeOutCreate = timeoutCreate;
            TimeOutUpdate = timeoutUpdate;
            TimeOutAccess = timeoutAccess;
        }

        public ExpiryPolicy CreateInstance()
        {
           return new ExpiryPolicy(TimeOutCreate,TimeOutUpdate,TimeOutAccess);
        }
    }
}