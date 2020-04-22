using Apache.Ignite.Core;
using Apache.Ignite.Core.Cache.Configuration;
using Apache.Ignite.Core.Configuration;
using Apache.Ignite.Core.Discovery.Tcp;
using Apache.Ignite.Core.Discovery.Tcp.Static;
using Microsoft.Extensions.Configuration;
using System;
using Apache.Ignite.Core.Cache;
using Apache.Ignite.Core.Events;
using Apache.Ignite.Core.PersistentStore;
using Apache.Ignite.Core.Ssl;
using CCM_API.Helpers;
using CCM_API.Logging;
using WalMode = Apache.Ignite.Core.PersistentStore.WalMode;

namespace CCM_API
{
    public class IgniteManager
    {
        public IgniteManager(IConfiguration configuration)
        {
            Configuration = configuration;

            SslContextFactory factory = new SslContextFactory()
            {
                KeyStorePassword = "ccm-ssl",
                KeyStoreFilePath = "Ssl/keystore.jks",
                TrustStorePassword = "ccm-ssl",
                TrustStoreFilePath = "Ssl/trust.jks",
            };

            igconfig = new IgniteConfiguration
            {
                Logger = new IgniteSerilog(),
                DiscoverySpi = new TcpDiscoverySpi
                {
                    IpFinder = new TcpDiscoveryStaticIpFinder
                    {
                        Endpoints = new[]
                        {
                            configuration["ignite:endpoints:master"]
                        }
                    },
                    SocketTimeout = TimeSpan.FromSeconds(0.5)
                },
                SslContextFactory = factory,
                IncludedEventTypes = EventType.CacheAll,
                JvmOptions = new[] { "-Xms" + configuration["ignite:jvm:ms"], "-Xmx" + configuration["ignite:jvm:mx"] },
                AuthenticationEnabled = true,
                DataStorageConfiguration = new DataStorageConfiguration
                {
                    WalMode = Apache.Ignite.Core.Configuration.WalMode.Fsync,
                    WalFlushFrequency = TimeSpan.FromSeconds(5),
                    DefaultDataRegionConfiguration = new DataRegionConfiguration
                    {
                        Name = "defaultRegion",
                        PersistenceEnabled = true,
                        InitialSize = 128 * 1024 * 1024,  // 128 MB,
                        MaxSize = 4L * 1024 * 1024 * 1025  // 4 GB
                    },
                    DataRegionConfigurations = new[]
                    {
                        new DataRegionConfiguration
                        {
                            Name = "ccmMetaData",
                            PersistenceEnabled = true,
                            InitialSize = 32 * 1024 * 1024,  // 32 MB,
                            MaxSize = 512 * 1024 * 1025  // 512 MB
                        },
                        new DataRegionConfiguration
                        {
                            // Persistence is off by default.
                            Name = "inMemoryRegion",
                            PersistenceEnabled = false,
                            InitialSize = 32 * 1024 * 1024,  // 32 MB,
                            MaxSize = 512 * 1024 * 1025  // 512 MB
                        }
                    }
                },
                CacheConfiguration = IgniteCacheHelper.GetAllCaches()
            };
        }

        public IConfiguration Configuration { get; }

        private IIgnite ignition;

        private IgniteConfiguration igconfig;
        
        public void StartIgnite()
        {
            if (ignition == null)
            {
                ignition = Ignition.Start(igconfig);
                ignition.GetCluster().SetActive(true);
            }
            else
            {
                if(!ignition.GetCluster().IsActive()) ignition = Ignition.Start(igconfig);
            }    
            
        }

        public ICache<string,T> GetDataStorage<T>()
        {
            var storage = ignition.GetOrCreateCache<string, T>("default");
            return storage;
        }
        
        public ICache<string,object> GetMetaDataStorage()
        {
            var storage = ignition.GetOrCreateCache<string, object>("metaData");
            return storage;
        }

        public void StopIgnite()
        {
            Ignition.StopAll(true);
        }

        public IIgnite GetIgnition()
        {
            StartIgnite();
            return ignition;
        }
    }
}