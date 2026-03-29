@extends('layouts.app')

@section('title', 'Organizational Structure')

@section('content')
<div class="container-fluid">
    <div class="row">
        @include('admin.partials.sidebar')
        <main class="col-md-10 py-4">
            <div class="admin-back-btn-wrap mb-3">
                @if(isset($organization))
                    <a href="{{ route('admin.organizations.profile', $organization->id) }}" class="btn btn-secondary rounded-pill px-3">&lt; Back to Organization Profile</a>
                @else
                    <a href="{{ route('admin.dashboard') }}" class="btn btn-secondary rounded-pill px-3">&lt; Back</a>
                @endif
            </div>

            <div class="py-3">
                @if(isset($organization))
                    <h1 class="h4 mb-4">{{ $organization->name }} - Organizational Structure</h1>
                    <!-- Organizational Structure Chart -->
                    <div id="orgChartContainer" style="position: relative; width: 100%; height: 700px; background: #F5F5F5; overflow: auto; overflow-x: auto; overflow-y: auto;">
                        <div id="orgChart" style="width: max-content; min-width: 100%; height: max-content; min-height: 100%;"></div>
                    </div>
                @else
                    <h1 class="h4 mb-4">OSA Central Hub - Organizational Structure</h1>
                    
                    <!-- Plain Organizational Structure -->
                    @if(isset($structureData) && $structureData)
                        <div class="org-structure-plain">
                            <!-- OSA Head (Level 0) -->
                            @if($structureData['admin'])
                                <div class="org-level level-0">
                                    <div class="org-box org-admin">
                                        <div class="org-image-container">
                                            @if($structureData['admin']['image'])
                                                <img src="{{ $structureData['admin']['image'] }}" alt="{{ $structureData['admin']['name'] }}" class="org-image" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                                <div class="org-image-placeholder" style="display: none;">👤</div>
                                            @else
                                                <div class="org-image-placeholder">👤</div>
                                            @endif
                                        </div>
                                        <div class="org-name">{{ $structureData['admin']['name'] }}</div>
                                        <div class="org-designation">{{ $structureData['admin']['designation'] }}</div>
                                    </div>
                                </div>
                                
                                <!-- Connection Line -->
                                <div class="org-connector"></div>
                            @endif

                            <!-- OSA Staff (Level 1) -->
                            @if(!empty($structureData['osaStaff']))
                                <div class="org-level level-1">
                                    <div class="org-staff-row">
                                        @foreach($structureData['osaStaff'] as $index => $staff)
                                            <div class="org-staff-group" data-staff-group="osa-{{ $index }}">
                                                <!-- OSA Staff Member -->
                                                <div class="org-box org-osa-staff org-staff-clickable" data-toggle-assistants="osa-{{ $index }}">
                                                    <div class="org-image-container">
                                                        @if($staff['image'])
                                                            <img src="{{ $staff['image'] }}" alt="{{ $staff['name'] }}" class="org-image" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                                            <div class="org-image-placeholder" style="display: none;">👤</div>
                                                        @else
                                                            <div class="org-image-placeholder">👤</div>
                                                        @endif
                                                    </div>
                                                    <div class="org-name">{{ $staff['name'] }}</div>
                                                    <div class="org-designation">{{ $staff['designation'] }}</div>
                                                    @if(!empty($staff['organizations']) || !empty($staff['assistants']))
                                                        <div class="org-toggle-indicator">▼ Click to view organizations</div>
                                                    @endif
                                                </div>
                                                
                                                <!-- Organizations (Level 2) - Hidden by default -->
                                                @if(!empty($staff['organizations']) || !empty($staff['assistants']))
                                                    <div class="org-organizations-container" id="organizations-osa-{{ $index }}" style="display: none;">
                                                        <div class="org-connector-small"></div>
                                                        <div class="org-organizations-row">
                                                            @if(!empty($staff['organizations']))
                                                                @foreach($staff['organizations'] as $org)
                                                                    <div class="org-box org-organization org-org-clickable" 
                                                                         data-toggle-assistants="osa-{{ $index }}-org-{{ $org['id'] }}"
                                                                         data-org-name="{{ $org['name'] }}">
                                                                        <div class="org-name">{{ $org['name'] }}</div>
                                                                        <div class="org-toggle-indicator-small">▼ Click to view assistants</div>
                                                                    </div>
                                                                @endforeach
                                                            @else
                                                                @php
                                                                    // If no organizations in staff data, get unique organizations from assistants
                                                                    $orgsFromAssistants = [];
                                                                    if (!empty($staff['assistants'])) {
                                                                        foreach ($staff['assistants'] as $orgName => $orgAssistants) {
                                                                            $orgsFromAssistants[] = [
                                                                                'name' => $orgName,
                                                                                'id' => $orgAssistants[0]['organization_id'] ?? null
                                                                            ];
                                                                        }
                                                                    }
                                                                @endphp
                                                                @foreach($orgsFromAssistants as $org)
                                                                    <div class="org-box org-organization org-org-clickable" 
                                                                         data-toggle-assistants="osa-{{ $index }}-org-{{ $org['id'] ?? md5($org['name']) }}"
                                                                         data-org-name="{{ $org['name'] }}">
                                                                        <div class="org-name">{{ $org['name'] }}</div>
                                                                        <div class="org-toggle-indicator-small">▼ Click to view assistants</div>
                                                                    </div>
                                                                @endforeach
                                                            @endif
                                                        </div>
                                                    </div>
                                                    
                                                    <!-- Student Leaders (Level 3) - Hidden by default, shown when organization is clicked -->
                                                    @if(!empty($staff['assistants']))
                                                        @foreach($staff['assistants'] as $orgName => $orgAssistants)
                                                            @php
                                                                $orgId = $orgAssistants[0]['organization_id'] ?? md5($orgName);
                                                            @endphp
                                                            <div class="org-assistants-container" id="assistants-osa-{{ $index }}-org-{{ $orgId }}" style="display: none;">
                                                                <div class="org-connector-small"></div>
                                                                <div class="org-assistants-group">
                                                                    <div class="org-assistants-org-header">{{ $orgName }}</div>
                                                                    <div class="org-assistants-row">
                                                                        @foreach($orgAssistants as $assistant)
                                                                            <div class="org-box org-assistant">
                                                                                <div class="org-name">{{ $assistant['name'] }}</div>
                                                                                <div class="org-position">{{ $assistant['position'] ?? 'Student Leader' }}</div>
                                                                            </div>
                                                                        @endforeach
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        @endforeach
                                                    @endif
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            <!-- Staff with Designations (Level 1) -->
                            @if(!empty($structureData['designationStaff']))
                                <div class="org-level level-1">
                                    <div class="org-staff-row">
                                        @foreach($structureData['designationStaff'] as $index => $staff)
                                            <div class="org-staff-group" data-staff-group="{{ $index }}">
                                                <!-- Staff with Designation -->
                                                <div class="org-box org-designation-staff org-staff-clickable" data-toggle-assistants="{{ $index }}">
                                                    <div class="org-image-container">
                                                        @if($staff['image'])
                                                            <img src="{{ $staff['image'] }}" alt="{{ $staff['name'] }}" class="org-image" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                                            <div class="org-image-placeholder" style="display: none;">👤</div>
                                                        @else
                                                            <div class="org-image-placeholder">👤</div>
                                                        @endif
                                                    </div>
                                                    <div class="org-name">{{ $staff['name'] }}</div>
                                                    <div class="org-designation">{{ $staff['designation'] }}</div>
                                                    @if(!empty($staff['organizations']) || !empty($staff['assistants']))
                                                        <div class="org-toggle-indicator">▼ Click to view organizations</div>
                                                    @endif
                                                </div>
                                                
                                                <!-- Organizations (Level 2) - Hidden by default -->
                                                @if(!empty($staff['organizations']) || !empty($staff['assistants']))
                                                    <div class="org-organizations-container" id="organizations-{{ $index }}" style="display: none;">
                                                        <div class="org-connector-small"></div>
                                                        <div class="org-organizations-row">
                                                            @if(!empty($staff['organizations']))
                                                                @foreach($staff['organizations'] as $org)
                                                                    <div class="org-box org-organization org-org-clickable" 
                                                                         data-toggle-assistants="{{ $index }}-org-{{ $org['id'] }}"
                                                                         data-org-name="{{ $org['name'] }}">
                                                                        <div class="org-name">{{ $org['name'] }}</div>
                                                                        <div class="org-toggle-indicator-small">▼ Click to view assistants</div>
                                                                    </div>
                                                                @endforeach
                                                            @else
                                                                @php
                                                                    // If no organizations in staff data, get unique organizations from assistants
                                                                    $orgsFromAssistants = [];
                                                                    if (!empty($staff['assistants'])) {
                                                                        foreach ($staff['assistants'] as $orgName => $orgAssistants) {
                                                                            $orgsFromAssistants[] = [
                                                                                'name' => $orgName,
                                                                                'id' => $orgAssistants[0]['organization_id'] ?? null
                                                                            ];
                                                                        }
                                                                    }
                                                                @endphp
                                                                @foreach($orgsFromAssistants as $org)
                                                                    <div class="org-box org-organization org-org-clickable" 
                                                                         data-toggle-assistants="{{ $index }}-org-{{ $org['id'] ?? md5($org['name']) }}"
                                                                         data-org-name="{{ $org['name'] }}">
                                                                        <div class="org-name">{{ $org['name'] }}</div>
                                                                        <div class="org-toggle-indicator-small">▼ Click to view assistants</div>
                                                                    </div>
                                                                @endforeach
                                                            @endif
                                                        </div>
                                                    </div>
                                                    
                                                    <!-- Student Leaders (Level 3) - Hidden by default, shown when organization is clicked -->
                                                    @if(!empty($staff['assistants']))
                                                        @foreach($staff['assistants'] as $orgName => $orgAssistants)
                                                            @php
                                                                $orgId = $orgAssistants[0]['organization_id'] ?? md5($orgName);
                                                            @endphp
                                                            <div class="org-assistants-container" id="assistants-{{ $index }}-org-{{ $orgId }}" style="display: none;">
                                                                <div class="org-connector-small"></div>
                                                                <div class="org-assistants-group">
                                                                    <div class="org-assistants-org-header">{{ $orgName }}</div>
                                                                    <div class="org-assistants-row">
                                                                        @foreach($orgAssistants as $assistant)
                                                                            <div class="org-box org-assistant">
                                                                                <div class="org-name">{{ $assistant['name'] }}</div>
                                                                                <div class="org-position">{{ $assistant['position'] ?? 'Student Leader' }}</div>
                                                                            </div>
                                                                        @endforeach
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        @endforeach
                                                    @endif
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>
                    @endif
                @endif
            </div>
        </main>
    </div>
</div>

<!-- vis-network.js for Organizational Chart -->
<script type="text/javascript" src="https://unpkg.com/vis-network/standalone/umd/vis-network.min.js"></script>
<link rel="stylesheet" type="text/css" href="https://unpkg.com/vis-network/styles/vis-network.min.css">

<script>
@if(isset($organization))
// Organization Staff to Assistants Organizational Structure
document.addEventListener('DOMContentLoaded', function() {
    var orgData = @json($orgStructure ?? ['nodes' => [], 'edges' => []]);
    
    if (orgData.nodes && orgData.nodes.length > 0) {
        var nodes = new vis.DataSet(orgData.nodes.map(function(node) {
            // Apply group-specific styling
            var nodeStyle = {
                shape: 'box',
                font: {
                    size: 14,
                    face: 'Arial',
                    color: '#ffffff'
                },
                borderWidth: 2,
                shadow: true,
                margin: 10
            };

            // Apply colors based on group
            if (node.group === 'organization') {
                nodeStyle.color = {
                    background: '#4CAF50',
                    border: '#2E7D32',
                    highlight: {
                        background: '#66BB6A',
                        border: '#388E3C'
                    }
                };
                nodeStyle.font.size = 16;
            } else if (node.group === 'staff') {
                nodeStyle.color = {
                    background: '#2196F3',
                    border: '#1565C0',
                    highlight: {
                        background: '#42A5F5',
                        border: '#1976D2'
                    }
                };
                nodeStyle.font.size = 14;
            } else if (node.group === 'assistant') {
                nodeStyle.color = {
                    background: '#FF9800',
                    border: '#E65100',
                    highlight: {
                        background: '#FFB74D',
                        border: '#F57C00'
                    }
                };
                nodeStyle.font.size = 12;
            }

            return Object.assign(node, nodeStyle);
        }));

        var edges = new vis.DataSet(orgData.edges.map(function(edge) {
            return {
                from: edge.from,
                to: edge.to,
                arrows: {
                    to: {
                        enabled: true,
                        scaleFactor: 1.2,
                        type: 'arrow'
                    }
                },
                color: {
                    color: '#848484',
                    highlight: '#2B7CE9'
                },
                smooth: {
                    type: 'cubicBezier',
                    forceDirection: 'vertical',
                    roundness: 0.4
                },
                width: 2
            };
        }));

        var data = {
            nodes: nodes,
            edges: edges
        };

        var options = {
            layout: {
                hierarchical: {
                    direction: 'UD', // Up to Down
                    sortMethod: 'directed',
                    levelSeparation: 120,
                    nodeSpacing: 150,
                    treeSpacing: 200,
                    blockShifting: true,
                    edgeMinimization: true,
                    parentCentralization: true
                }
            },
            physics: {
                enabled: false // Disable physics for hierarchical layout
            },
            interaction: {
                dragNodes: true,
                dragView: true,
                zoomView: true,
                hover: true
            },
            nodes: {
                chosen: {
                    node: function(values, id, selected, hovering) {
                        if (hovering) {
                            values.borderWidth = 4;
                        }
                    }
                }
            }
        };

        var container = document.getElementById('orgChart');
        if (container) {
            var network = new vis.Network(container, data, options);

            // Make responsive
            window.addEventListener('resize', function() {
                network.fit();
            });

            // Fit on load
            network.once('ready', function() {
                network.fit();
            });

            // Add click event to show details
            network.on('click', function(params) {
                if (params.nodes.length > 0) {
                    var nodeId = params.nodes[0];
                    var nodeData = nodes.get(nodeId);
                    if (nodeData && nodeData.title) {
                        console.log('Selected:', nodeData.title);
                    }
                }
            });
        }
    } else {
        // Show message if no data
        var container = document.getElementById('orgChart');
        if (container) {
            container.innerHTML = '<div style="display: flex; align-items: center; justify-content: center; height: 100%; color: #666;"><p>No organizational structure data available for this organization.</p></div>';
        }
    }
});
@else
// Admin to Staff Organizational Structure (only render if plain structure is not available)
@if(!isset($structureData) || !$structureData)
document.addEventListener('DOMContentLoaded', function() {
    // Helper function to escape HTML
    function escapeHtml(text) {
        var map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return text.replace(/[&<>"']/g, function(m) { return map[m]; });
    }
    
    var orgData = @json($orgStructure);
    
    // Debug: log the data structure
    console.log('Organizational Structure Data:', orgData);
    console.log('Nodes count:', orgData.nodes ? orgData.nodes.length : 0);
    
    if (orgData.nodes && orgData.nodes.length > 0) {
        var nodes = new vis.DataSet(orgData.nodes.map(function(node) {
            // Apply group-specific styling
            var nodeStyle = {
                font: {
                    size: 12,
                    face: 'Arial',
                    color: '#333333',
                    align: 'center'
                },
                borderWidth: 2,
                shadow: true,
                margin: 10,
                labelHighlightBold: true
            };

            // Apply colors and styling based on group
            if (node.group === 'admin') {
                // Admin node - blue-purple with white body
                nodeStyle.shape = 'box';
                nodeStyle.color = {
                    background: '#9C27B0', // Purple header
                    border: '#7B1FA2',
                    highlight: {
                        background: '#BA68C8',
                        border: '#8E24AA'
                    }
                };
                nodeStyle.font.size = 14;
                nodeStyle.font.color = '#ffffff';
                nodeStyle.widthConstraint = { maximum: 220 };
                nodeStyle.heightConstraint = { maximum: 120 };
                nodeStyle.shapeProperties = {
                    borderRadius: 5
                };
            } else if (node.group === 'staff') {
                // Staff nodes - square containers with purple border
                nodeStyle.shape = 'box';
                nodeStyle.color = {
                    background: '#FFFFFF', // White background
                    border: '#9C27B0', // Purple border
                    highlight: {
                        background: '#F3E5F5',
                        border: '#7B1FA2'
                    }
                };
                nodeStyle.font.size = 10;
                nodeStyle.font.color = '#333333';
                nodeStyle.widthConstraint = { maximum: 180 };
                nodeStyle.heightConstraint = { maximum: 160 };
                nodeStyle.shapeProperties = {
                    borderRadius: 5
                };
                
                // Create custom HTML label with circular image container inside square
                var labelParts = node.label.split('\\n');
                var staffName = labelParts[0] || 'Member name';
                var designation = labelParts[1] || 'Role name';
                var dept = node.department || 'Organization/Department name';
                var imageUrl = node.image || null;
                
                // Build custom HTML label with circular image container
                var imageContainer = '';
                if (imageUrl) {
                    // Use img tag with proper error handling
                    imageContainer = '<div style="width: 60px; height: 60px; border-radius: 50%; margin: 0 auto 10px; overflow: hidden; border: 2px solid #9C27B0; background: #E0E0E0; position: relative;">' +
                        '<img src="' + escapeHtml(imageUrl) + '" style="width: 100%; height: 100%; object-fit: cover; display: block;" ' +
                        'onerror="this.onerror=null; this.style.display=\'none\'; this.parentElement.innerHTML=\'<div style=\'width:100%;height:100%;display:flex;align-items:center;justify-content:center;background:#E0E0E0;color:#666;font-size:24px;\'>👤</div>\';">' +
                        '</div>';
                } else {
                    imageContainer = '<div style="width: 60px; height: 60px; border-radius: 50%; margin: 0 auto 10px; overflow: hidden; border: 2px solid #9C27B0; background: #E0E0E0; display: flex; align-items: center; justify-content: center; color: #666; font-size: 24px;">👤</div>';
                }
                
                node.label = '<div style="text-align: center; padding: 10px; width: 180px; box-sizing: border-box; font-family: Arial, sans-serif;">' +
                    imageContainer +
                    '<div style="font-weight: bold; font-size: 11px; margin-bottom: 4px; color: #333; line-height: 1.2; word-wrap: break-word;">' + escapeHtml(staffName) + '</div>' +
                    '<div style="font-size: 10px; color: #666; margin-bottom: 2px; line-height: 1.2; word-wrap: break-word;">' + escapeHtml(designation) + '</div>' +
                    '<div style="font-size: 9px; color: #999; line-height: 1.2; word-wrap: break-word;">' + escapeHtml(dept) + '</div>' +
                    '</div>';
                node.labelType = 'html';
            } else if (node.group === 'assistant') {
                // Student Leader nodes - smaller boxes with orange border
                nodeStyle.shape = 'box';
                nodeStyle.color = {
                    background: '#FFFFFF', // White background
                    border: '#FF9800', // Orange border
                    highlight: {
                        background: '#FFF3E0',
                        border: '#F57C00'
                    }
                };
                nodeStyle.font.size = 9;
                nodeStyle.font.color = '#333333';
                nodeStyle.widthConstraint = { maximum: 160 };
                nodeStyle.heightConstraint = { maximum: 140 };
                nodeStyle.shapeProperties = {
                    borderRadius: 5
                };
                
                // Create custom HTML label with circular image container
                var labelParts = node.label.split('\\n');
                var assistantName = labelParts[0] || 'Assistant name';
                var position = labelParts[1] || 'Student Leader';
                var dept = node.department || 'No Department';
                var imageUrl = node.image || null;
                
                // Build custom HTML label with circular image container
                var imageContainer = '';
                if (imageUrl) {
                    imageContainer = '<div style="width: 50px; height: 50px; border-radius: 50%; margin: 0 auto 8px; overflow: hidden; border: 2px solid #FF9800; background: #E0E0E0; position: relative;">' +
                        '<img src="' + escapeHtml(imageUrl) + '" style="width: 100%; height: 100%; object-fit: cover; display: block;" ' +
                        'onerror="this.onerror=null; this.style.display=\'none\'; this.parentElement.innerHTML=\'<div style=\'width:100%;height:100%;display:flex;align-items:center;justify-content:center;background:#E0E0E0;color:#666;font-size:20px;\'>👤</div>\';">' +
                        '</div>';
                } else {
                    imageContainer = '<div style="width: 50px; height: 50px; border-radius: 50%; margin: 0 auto 8px; overflow: hidden; border: 2px solid #FF9800; background: #E0E0E0; display: flex; align-items: center; justify-content: center; color: #666; font-size: 20px;">👤</div>';
                }
                
                node.label = '<div style="text-align: center; padding: 8px; width: 160px; box-sizing: border-box; font-family: Arial, sans-serif;">' +
                    imageContainer +
                    '<div style="font-weight: bold; font-size: 10px; margin-bottom: 3px; color: #333; line-height: 1.2; word-wrap: break-word;">' + escapeHtml(assistantName) + '</div>' +
                    '<div style="font-size: 9px; color: #666; margin-bottom: 2px; line-height: 1.2; word-wrap: break-word;">' + escapeHtml(position) + '</div>' +
                    '<div style="font-size: 8px; color: #999; line-height: 1.2; word-wrap: break-word;">' + escapeHtml(dept) + '</div>' +
                    '</div>';
                node.labelType = 'html';
            }

            // Preserve original node properties
            return Object.assign({}, node, nodeStyle);
        }));

        var edges = new vis.DataSet(orgData.edges.map(function(edge) {
            return {
                from: edge.from,
                to: edge.to,
                arrows: {
                    to: {
                        enabled: true,
                        scaleFactor: 1.2,
                        type: 'arrow'
                    }
                },
                color: {
                    color: '#848484',
                    highlight: '#2B7CE9'
                },
                smooth: {
                    type: 'cubicBezier',
                    forceDirection: 'vertical',
                    roundness: 0.4
                },
                width: 2
            };
        }));

        var data = {
            nodes: nodes,
            edges: edges
        };

        var options = {
            layout: {
                hierarchical: {
                    direction: 'LR', // Left to Right (columns)
                    sortMethod: 'directed',
                    levelSeparation: 250, // Horizontal spacing between levels (columns)
                    nodeSpacing: 180, // Vertical spacing between nodes at same level
                    treeSpacing: 200, // Spacing between subtrees
                    blockShifting: true,
                    edgeMinimization: true,
                    parentCentralization: true,
                    shakeTowards: 'leaves'
                }
            },
            physics: {
                enabled: false // Disable physics for hierarchical layout
            },
            interaction: {
                dragNodes: true,
                dragView: true,
                zoomView: true,
                hover: true,
                tooltipDelay: 200
            },
            nodes: {
                chosen: {
                    node: function(values, id, selected, hovering) {
                        if (hovering) {
                            values.borderWidth = 3;
                            values.shadow = true;
                        }
                    }
                },
                margin: 12,
                shapeProperties: {
                    borderRadius: 5
                }
            },
            edges: {
                length: 200,
                color: {
                    color: '#424242',
                    highlight: '#7B1FA2'
                },
                width: 2,
                smooth: false, // Straight lines instead of curves
                arrows: {
                    to: {
                        enabled: true,
                        scaleFactor: 1.2,
                        type: 'arrow'
                    }
                },
                font: {
                    size: 12,
                    align: 'middle'
                }
            }
        };

        var container = document.getElementById('adminOrgChart');
        var network = new vis.Network(container, data, options);

        // Make responsive
        window.addEventListener('resize', function() {
            if (network) {
                network.fit();
            }
        });

        // Don't auto-fit on load - let structure expand for scrollbars
        network.once('ready', function() {
            // Allow the network to expand beyond viewport
            // The container will show scrollbars when needed
        });

        // Add click event to show details and organization links
        network.on('click', function(params) {
            if (params.nodes.length > 0) {
                var nodeId = params.nodes[0];
                var nodeData = nodes.get(nodeId);
                if (nodeData) {
                    // Always show modal with full details
                    showStaffDetailsModal(nodeData);
                }
            }
        });
        
        // Function to show staff details modal
        function showStaffDetailsModal(nodeData) {
            var staffName = nodeData.label ? nodeData.label.split('\\n')[0] : 'N/A';
            var designation = nodeData.label && nodeData.label.split('\\n').length > 1 ? 
                             nodeData.label.split('\\n')[1] : 'N/A';
            var title = nodeData.title || '';
            
            var modalHtml = '<div class="modal fade" id="staffDetailsModal" tabindex="-1" role="dialog" aria-labelledby="staffDetailsModalLabel" aria-hidden="true">' +
                '<div class="modal-dialog modal-dialog-centered" role="document">' +
                '<div class="modal-content">' +
                '<div class="modal-header" style="background-color: midnightblue; color: white;">' +
                '<h5 class="modal-title" id="staffDetailsModalLabel">Staff Details</h5>' +
                '<button type="button" class="close" data-dismiss="modal" aria-label="Close" style="color: white;">' +
                '<span aria-hidden="true">&times;</span>' +
                '</button>' +
                '</div>' +
                '<div class="modal-body">' +
                '<p><strong>Name:</strong> ' + staffName + '</p>' +
                '<p><strong>Designation:</strong> ' + designation + '</p>';
            
            if (title) {
                var titleLines = title.split('\\n');
                titleLines.forEach(function(line) {
                    if (line.trim() && line.includes(':')) {
                        modalHtml += '<p><strong>' + line.split(':')[0] + ':</strong> ' + 
                                   line.split(':').slice(1).join(':').trim() + '</p>';
                    }
                });
            }
            
            if (nodeData.organizations && nodeData.organizations.length > 0) {
                modalHtml += '<hr><p><strong>Click on an organization to view its structure:</strong></p>' +
                    '<ul class="list-group">';
                
                nodeData.organizations.forEach(function(org) {
                    var orgStructureUrl = '{{ route("admin.organizational-structure") }}?organization_id=' + org.id;
                    modalHtml += '<li class="list-group-item">' +
                        '<a href="' + orgStructureUrl + '" class="btn btn-link p-0" style="color: midnightblue; text-decoration: none;">' +
                        '<i class="bi bi-building"></i> ' + org.name +
                        '</a>' +
                        '</li>';
                });
                
                modalHtml += '</ul>';
            } else {
                modalHtml += '<p><em>No organizations assigned</em></p>';
            }
            
            modalHtml += '</div>' +
                '<div class="modal-footer">' +
                '<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>' +
                '</div>' +
                '</div>' +
                '</div>' +
                '</div>';
            
            // Remove existing modal if any
            var existingModal = document.getElementById('staffDetailsModal');
            if (existingModal) {
                existingModal.remove();
            }
            
            // Add modal to body
            document.body.insertAdjacentHTML('beforeend', modalHtml);
            
            // Show modal
            $('#staffDetailsModal').modal('show');
            
            // Remove modal from DOM when closed
            $('#staffDetailsModal').on('hidden.bs.modal', function() {
                $(this).remove();
            });
        }
    } else {
        // Show message if no data
        var container = document.getElementById('adminOrgChart');
        if (container) {
            container.innerHTML = '<div style="display: flex; align-items: center; justify-content: center; height: 100%; color: #666;"><p>No organizational structure data available.</p></div>';
        }
    }
});
@endif
@endif

@if(isset($structureData) && $structureData)
// Toggle organizations visibility on staff click, then assistants on organization click
document.addEventListener('DOMContentLoaded', function() {
    // Handle staff click - show/hide organizations
    const staffBoxes = document.querySelectorAll('.org-staff-clickable');
    
    staffBoxes.forEach(function(staffBox) {
        staffBox.addEventListener('click', function() {
            const toggleId = this.getAttribute('data-toggle-assistants');
            const organizationsContainer = document.getElementById('organizations-' + toggleId);
            const indicator = this.querySelector('.org-toggle-indicator');
            
            if (organizationsContainer) {
                if (organizationsContainer.style.display === 'none' || organizationsContainer.style.display === '') {
                    organizationsContainer.style.display = 'flex';
                    if (indicator) {
                        indicator.textContent = '▲ Click to hide organizations';
                    }
                } else {
                    organizationsContainer.style.display = 'none';
                    // Also hide all assistants containers for this staff
                    const allAssistantContainers = document.querySelectorAll('[id^="assistants-' + toggleId + '-org-"]');
                    allAssistantContainers.forEach(function(container) {
                        container.style.display = 'none';
                    });
                    // Reset organization indicators
                    const orgBoxes = organizationsContainer.querySelectorAll('.org-org-clickable');
                    orgBoxes.forEach(function(orgBox) {
                        const orgIndicator = orgBox.querySelector('.org-toggle-indicator-small');
                        if (orgIndicator) {
                            orgIndicator.textContent = '▼ Click to view assistants';
                        }
                    });
                    if (indicator) {
                        indicator.textContent = '▼ Click to view organizations';
                    }
                }
            }
        });
    });
    
    // Handle organization click - show/hide assistants for that organization
    const orgBoxes = document.querySelectorAll('.org-org-clickable');
    
    orgBoxes.forEach(function(orgBox) {
        orgBox.addEventListener('click', function(e) {
            e.stopPropagation(); // Prevent triggering staff click
            const toggleId = this.getAttribute('data-toggle-assistants');
            const assistantsContainer = document.getElementById('assistants-' + toggleId);
            const indicator = this.querySelector('.org-toggle-indicator-small');
            
            if (assistantsContainer) {
                if (assistantsContainer.style.display === 'none' || assistantsContainer.style.display === '') {
                    assistantsContainer.style.display = 'flex';
                    if (indicator) {
                        indicator.textContent = '▲ Click to hide assistants';
                    }
                } else {
                    assistantsContainer.style.display = 'none';
                    if (indicator) {
                        indicator.textContent = '▼ Click to view assistants';
                    }
                }
            }
        });
    });
});
@endif
</script>

<style>
@if(isset($organization))
#orgChart {
    min-height: 400px;
}

@media (max-width: 768px) {
    #orgChart {
        height: 400px !important;
    }
}

@media (max-width: 576px) {
    #orgChart {
        height: 350px !important;
    }
}
@else
/* Plain Organizational Structure Styles */
.org-structure-plain {
    display: flex;
    flex-direction: column;
    align-items: center;
    padding: 2rem 1rem;
    min-height: 600px;
}

.org-level {
    display: flex;
    justify-content: center;
    width: 100%;
    margin-bottom: 2rem;
}

.org-level.level-0 {
    margin-bottom: 1.5rem;
}

.org-staff-row {
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    gap: 1.5rem;
    width: 100%;
}

.org-staff-group {
    display: flex;
    flex-direction: column;
    align-items: center;
    margin: 0 1rem;
}

.org-assistants-row {
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    gap: 1rem;
    margin-top: 0.5rem;
    max-width: 800px;
}

.org-organizations-container {
    width: 100%;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 1rem;
}

.org-organizations-row {
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    gap: 1rem;
    margin-top: 0.5rem;
    max-width: 800px;
}

.org-organization {
    border-color: #2196F3;
    background: #E3F2FD;
    min-width: 180px;
    max-width: 200px;
    padding: 1rem;
    cursor: pointer;
    user-select: none;
}

.org-organization:hover {
    background: #BBDEFB !important;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.org-organization .org-name {
    color: midnightblue;
    font-weight: 600;
    font-size: 0.9rem;
}

.org-toggle-indicator-small {
    font-size: 0.7rem;
    color: #1976D2;
    margin-top: 0.5rem;
    font-style: italic;
}

.org-assistants-container {
    width: 100%;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 1.5rem;
}

.org-assistants-group {
    width: 100%;
    display: flex;
    flex-direction: column;
    align-items: center;
}

.org-assistants-org-header {
    font-weight: bold;
    font-size: 0.95rem;
    color: midnightblue;
    background-color: #E3F2FD;
    padding: 0.5rem 1rem;
    border-radius: 4px;
    margin-bottom: 0.5rem;
    text-align: center;
    width: fit-content;
    min-width: 200px;
}

.org-box {
    background: #FFFFFF;
    border: 2px solid;
    border-radius: 8px;
    padding: 1rem;
    text-align: center;
    min-width: 180px;
    max-width: 200px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    transition: transform 0.2s, box-shadow 0.2s;
}

.org-box:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.org-admin {
    border-color: #2196F3;
    background: #2196F3;
    color: white;
    min-width: 220px;
    max-width: 250px;
}

.org-osa-staff {
    border-color: #4CAF50;
    background: #FFFFFF;
}

.org-designation-staff {
    border-color: #9C27B0;
    background: #FFFFFF;
}

.org-assistant {
    border-color: #FF9800;
    background: #FFFFFF;
    min-width: 150px;
    max-width: 170px;
    padding: 0.75rem;
}

.org-assistant .org-image-container {
    display: none;
}

.org-assistants-container {
    width: 100%;
    display: flex;
    flex-direction: column;
    align-items: center;
}

.org-staff-clickable {
    cursor: pointer;
    user-select: none;
}

.org-staff-clickable:hover {
    background: #F3E5F5 !important;
}

.org-toggle-indicator {
    font-size: 0.75rem;
    color: #9C27B0;
    margin-top: 0.5rem;
    font-style: italic;
}

.org-image-container {
    width: 80px;
    height: 80px;
    margin: 0 auto 0.75rem;
    border-radius: 50%;
    overflow: hidden;
    border: 3px solid;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #E0E0E0;
}

.org-admin .org-image-container {
    border-color: #FFFFFF;
    width: 100px;
    height: 100px;
}

.org-osa-staff .org-image-container {
    border-color: #4CAF50;
}

.org-designation-staff .org-image-container {
    border-color: #9C27B0;
}

.org-assistant .org-image-container {
    border-color: #FF9800;
    width: 60px;
    height: 60px;
}

.org-image {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
}

.org-image-placeholder {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2rem;
    color: #666;
}

.org-admin .org-image-placeholder {
    color: #FFFFFF;
    font-size: 2.5rem;
}

.org-assistant .org-image-placeholder {
    font-size: 1.5rem;
}

.org-name {
    font-weight: bold;
    font-size: 0.95rem;
    margin-bottom: 0.25rem;
    color: #333;
    word-wrap: break-word;
}

.org-admin .org-name {
    color: #FFFFFF;
    font-size: 1.1rem;
}

.org-assistant .org-name {
    font-size: 0.85rem;
}

.org-designation {
    font-size: 0.85rem;
    color: #666;
    word-wrap: break-word;
}

.org-admin .org-designation {
    color: #FFFFFF;
    font-size: 0.95rem;
}

.org-assistant .org-designation {
    font-size: 0.75rem;
}

.org-position {
    font-size: 0.8rem;
    color: #666;
    margin-top: 0.25rem;
}

.org-assistant .org-position {
    font-size: 0.75rem;
    color: #555;
    font-weight: 500;
}

.org-position {
    font-size: 0.8rem;
    color: #666;
    margin-top: 0.25rem;
}

.org-assistant .org-position {
    font-size: 0.75rem;
    color: #555;
    font-weight: 500;
}

.org-connector {
    width: 2px;
    height: 40px;
    background: #424242;
    margin: 0.5rem 0;
}

.org-connector-small {
    width: 2px;
    height: 20px;
    background: #424242;
    margin: 0.5rem 0;
}

@media (max-width: 992px) {
    .org-staff-row {
        gap: 1rem;
    }
    
    .org-assistants-row {
        gap: 0.75rem;
    }
    
    .org-box {
        min-width: 160px;
        max-width: 180px;
    }
    
    .org-assistant {
        min-width: 130px;
        max-width: 150px;
    }
}

@media (max-width: 768px) {
    .org-staff-row {
        gap: 0.75rem;
    }
    
    .org-box {
        min-width: 140px;
        max-width: 160px;
        padding: 0.75rem;
    }
    
    .org-image-container {
        width: 60px;
        height: 60px;
    }
    
    .org-admin .org-image-container {
        width: 80px;
        height: 80px;
    }
    
    .org-name {
        font-size: 0.85rem;
    }
    
    .org-designation {
        font-size: 0.75rem;
    }
}

@media (max-width: 576px) {
    .org-staff-row {
        flex-direction: column;
        align-items: center;
    }
    
    .org-staff-group {
        margin: 0.5rem 0;
    }
    
    .org-box {
        width: 100%;
        max-width: 250px;
    }
}
@endif
</style>

@endsection

