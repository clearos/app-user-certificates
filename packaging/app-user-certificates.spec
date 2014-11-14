
Name: app-user-certificates
Epoch: 1
Version: 1.6.7
Release: 1%{dist}
Summary: User Certificates
License: GPLv3
Group: ClearOS/Apps
Source: %{name}-%{version}.tar.gz
Buildarch: noarch
Requires: %{name}-core = 1:%{version}-%{release}
Requires: app-base
Requires: app-accounts
Requires: app-groups
Requires: app-certificate-manager

%description
Security certificates are used to secure various apps that you use on a day to day basis.

%package core
Summary: User Certificates - Core
License: LGPLv3
Group: ClearOS/Libraries
Requires: app-base-core
Requires: app-accounts-core
Requires: app-certificate-manager-core
Requires: app-user-certificates-plugin-core
Requires: system-users-driver

%description core
Security certificates are used to secure various apps that you use on a day to day basis.

This package provides the core API and libraries.

%prep
%setup -q
%build

%install
mkdir -p -m 755 %{buildroot}/usr/clearos/apps/user_certificates
cp -r * %{buildroot}/usr/clearos/apps/user_certificates/

install -D -m 0644 packaging/user_certificates.acl %{buildroot}/var/clearos/base/access_control/authenticated/user_certificates

%post
logger -p local6.notice -t installer 'app-user-certificates - installing'

%post core
logger -p local6.notice -t installer 'app-user-certificates-core - installing'

if [ $1 -eq 1 ]; then
    [ -x /usr/clearos/apps/user_certificates/deploy/install ] && /usr/clearos/apps/user_certificates/deploy/install
fi

[ -x /usr/clearos/apps/user_certificates/deploy/upgrade ] && /usr/clearos/apps/user_certificates/deploy/upgrade

exit 0

%preun
if [ $1 -eq 0 ]; then
    logger -p local6.notice -t installer 'app-user-certificates - uninstalling'
fi

%preun core
if [ $1 -eq 0 ]; then
    logger -p local6.notice -t installer 'app-user-certificates-core - uninstalling'
    [ -x /usr/clearos/apps/user_certificates/deploy/uninstall ] && /usr/clearos/apps/user_certificates/deploy/uninstall
fi

exit 0

%files
%defattr(-,root,root)
/usr/clearos/apps/user_certificates/controllers
/usr/clearos/apps/user_certificates/htdocs
/usr/clearos/apps/user_certificates/views

%files core
%defattr(-,root,root)
%exclude /usr/clearos/apps/user_certificates/packaging
%dir /usr/clearos/apps/user_certificates
/usr/clearos/apps/user_certificates/deploy
/usr/clearos/apps/user_certificates/language
/var/clearos/base/access_control/authenticated/user_certificates
